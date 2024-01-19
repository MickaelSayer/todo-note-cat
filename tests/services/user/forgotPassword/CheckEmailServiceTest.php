<?php

namespace App\Tests\services\security;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\tools\MailerService;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Services\user\forgotPassword\CheckEmailService;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckEmailServiceTest extends WebTestCase
{
    private MailerService|MockObject $mailerServiceMock;
    private UserRepository|MockObject $userRepositoryMock;
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;
    private ValidatorService|MockObject $validatorServiceMock;

    private CheckEmailService $checkEmailService;
    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['email'];

    private KernelBrowser $client;
    private ContainerInterface $container;
    private UserRepository $repository;
    private User $user;

    public function setUp(): void
    {
        $this->mailerServiceMock = $this->createMock(MailerService::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorServiceMock = $this->createMock(ValidatorService::class);

        $this->requesthelperTest = new RequestHelperTest();
        $this->checkEmailService = new CheckEmailService(
            $this->mailerServiceMock,
            $this->userRepositoryMock,
            $this->entityManagerMock,
            $this->tokenServiceMock,
            $this->validatorServiceMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];
    }

    /**
     * test the validation email
     */
    public function testvalidateEmailOK(): void
    {
        $this->requesthelperTest->setEmailRequest('email', $this->user->getEmail());
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $this->user->getEmail(), 'valid' => true])
            ->willReturn($this->user);

        $is_valid_email = $this->checkEmailService->validateEmail($request);

        $this->assertTrue($is_valid_email);
    }

    /**
     * test the validation email with the missing email in the request
     */
    public function testvalidateEmailMissinsEmailRequestKO(): void
    {
        $this->requesthelperTest->setEmailRequest('email', '');
        $request = $this->requesthelperTest->getRequest();

        $is_valid_email = $this->checkEmailService->validateEmail($request);
        $validationError = $this->checkEmailService->validationErrors;

        $this->assertNotEmpty($validationError);
        $this->assertFalse($is_valid_email);
    }

    /**
     * test the validation email with the Email Key not found
     */
    public function testvalidateEmailEmailKeyNotFoundRequestKO(): void
    {
        $this->requesthelperTest->setEmailRequest('test');
        $request = $this->requesthelperTest->getRequest();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->checkEmailService->validateEmail($request);
    }

    /**
     * test the validation email
     */
    public function testvalidateEmailEmptyUserKO(): void
    {
        $this->requesthelperTest->setEmailRequest();
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $requestContent['email'], 'valid' => true])
            ->willReturn(null);

        $is_valid_email = $this->checkEmailService->validateEmail($request);

        $this->assertFalse($is_valid_email);
    }

    /**
     * test the validation email with User email is not validated
     */
    public function testvalidateEmailUserEmailInvalidKO(): void
    {
        $user = $this->repository->findAll()[1];

        $this->requesthelperTest->setEmailRequest('email', $user->getEmail());
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $user->getEmail(), 'valid' => true])
            ->willReturn(null);

        $is_valid_email = $this->checkEmailService->validateEmail($request);

        $this->assertFalse($is_valid_email);
    }

    /**
     * test the validation email with Email not sent
     */
    public function testvalidateEmailErrorEmailNotSentKO(): void
    {
        $this->requesthelperTest->setEmailRequest('email', $this->user->getEmail());
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $this->user->getEmail(), 'valid' => true])
            ->willReturn($this->user);

        $this->mailerServiceMock
            ->expects($this->once())
            ->method('sendTemplateEmail')
            ->willThrowException(new TransportException('Error transport'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error transport');

        $this->checkEmailService->validateEmail($request);
    }
}
