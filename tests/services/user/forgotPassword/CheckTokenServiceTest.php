<?php

namespace App\Tests\services\security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\TokenHelperTest;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Services\user\forgotPassword\CheckTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

class CheckTokenServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;

    private TokenHelperTest $tokenHelper;
    private CheckTokenService $checkTokenService;
    private RequestHelperTest $requesthelperTest;

    private KernelBrowser $client;
    private ContainerInterface $container;
    private UserRepository $repository;
    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->checkTokenService = new CheckTokenService(
            $this->tokenServiceMock,
            $this->entityManagerMock
        );
        $this->requesthelperTest = new RequestHelperTest();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];
        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * test the validation email
     */
    public function testvalidateEmailOK(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setTokenRequestPost('token', $this->user->getToken());
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($requestContent['token'])
            ->willReturn($this->user);

        $is_valid = $this->checkTokenService->validateToken($request);

        $this->assertTrue($is_valid);
    }

    /**
     * test the validation email with an empty token in the request
     */
    public function testvalidateEmailRequestEmptyEmailKO(): void
    {
        $this->requesthelperTest->setTokenRequestPost('token', '');
        $request = $this->requesthelperTest->getRequest();

        $is_valid = $this->checkTokenService->validateToken($request);

        $this->assertFalse($is_valid);
    }

    /**
     * test the validation email with an bad token in the request
     */
    public function testvalidateEmailRequestBadEmailKO(): void
    {
        $this->requesthelperTest->setTokenRequestPost('token');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($requestContent['token'])
            ->willReturn(null);

        $is_valid = $this->checkTokenService->validateToken($request);

        $this->assertFalse($is_valid);
    }

    /**
     * test the validation email with an expirate token in the request
     */
    public function testvalidateEmailRequestExpirateEmailKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);

        $this->requesthelperTest->setTokenRequestPost('token', $this->user->getToken());
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('decodeJwtTokenUser')
            ->with($requestContent['token'])
            ->willThrowException(new JWTDecodeFailureException('expired_token', 'Error token expirate'));

        $this->expectException(JWTDecodeFailureException::class);
        $this->expectExceptionMessage('Error token expirate');

        $this->checkTokenService->validateToken($request);
    }
}
