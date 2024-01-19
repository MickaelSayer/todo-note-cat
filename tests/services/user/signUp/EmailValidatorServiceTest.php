<?php

namespace App\Tests\services\security;

use App\Entity\User;
use App\Tests\utils\TokenHelperTest;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Services\user\signUp\EmailValidatorService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailValidatorServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;

    private EmailValidatorService $emailValidatorService;
    private TokenHelperTest $tokenHelper;
    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['title', 'tasks'];

    private KernelBrowser $client;
    private ContainerInterface $container;
    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->emailValidatorService = new EmailValidatorService(
            $this->entityManagerMock,
            $this->tokenServiceMock
        );

        $this->requesthelperTest = new RequestHelperTest();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[1];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test the validation of the user email with success
     */
    public function testValidateEmailOK(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setTokenRequestPost('token', $this->user->getToken());
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($this->user->getToken())
            ->willReturn($this->user);

        $is_validate = $this->emailValidatorService->validateEmail($request);

        $this->assertTrue($is_validate);
    }

    /**
     * Test the validation of the user email with a missing token
     */
    public function testValidateEmailMissingTokenRequestKO(): void
    {
        $this->requesthelperTest->setTokenRequestPost('test');
        $request = $this->requesthelperTest->getRequest();

        $is_validate = $this->emailValidatorService->validateEmail($request);

        $this->assertFalse($is_validate);
    }

    /**
     * Test the validation of the user email with a token that does not correspond
     */
    public function testValidateEmailTokenRequestNotCorrespondKO(): void
    {
        $this->requesthelperTest->setTokenRequestPost('token');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($requestContent['token'])
            ->willReturn(null);

        $is_validate = $this->emailValidatorService->validateEmail($request);

        $this->assertFalse($is_validate);
    }

    /**
     * Test the validation of the user email with a bad token
     */
    public function testValidateEmailBadTokenRequestKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);
        $this->requesthelperTest->setTokenRequestPost('token', $this->user->getToken());
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($this->user->getToken())
            ->willReturn(null);

        $is_validate = $this->emailValidatorService->validateEmail($request);

        $this->assertFalse($is_validate);
    }
}
