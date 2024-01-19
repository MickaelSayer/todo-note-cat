<?php

namespace App\Tests\services\security;

use App\Services\tools\MailerService;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\user\signUp\CreateUserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;
    private UserPasswordHasherInterface|MockObject $passwordHasherMock;
    private MailerService|MockObject $mailerMock;
    private ValidatorService|MockObject $validatorServiceMock;

    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['email', 'password'];

    private CreateUserService $createUserService;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $this->mailerMock = $this->createMock(MailerService::class);
        $this->validatorServiceMock = $this->createMock(ValidatorService::class);

        $this->requesthelperTest = new RequestHelperTest();

        $this->createUserService = new CreateUserService(
            $this->passwordHasherMock,
            $this->validatorServiceMock,
            $this->entityManagerMock,
            $this->mailerMock,
            $this->tokenServiceMock
        );
    }

    /**
     * Test the creation of a user with success
     */
    public function testCreateUserOK(): void
    {
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $this->requesthelperTest->setEmailRequest('email', 'Test@test.test');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $is_create = $this->createUserService->create($request);

        $this->assertTrue($is_create);
    }

    /**
     * Test the creation of a user with The missing password in the request
     */
    public function testCreateUserMissingPasswordRequestKO(): void
    {
        $this->requesthelperTest->setPasswordRequest('test');
        $this->requesthelperTest->setEmailRequest('email', 'Test@test.test');
        $request = $this->requesthelperTest->getRequest();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->createUserService->create($request);
    }

    /**
     * Test the creation of a user with a bad password in the request
     */
    public function testCreateUserBadPasswordRequestKO(): void
    {
        $this->requesthelperTest->setPasswordRequest();
        $this->requesthelperTest->setEmailRequest('email', 'Test@test.test');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(['password' => 'Error password']);

        $is_create = $this->createUserService->create($request);
        $errors = $this->createUserService->validationErrors;

        $this->assertNotEmpty($errors);
        $this->assertFalse($is_create);
    }

    /**
     * Test the creation of a user with a missing email in the request
     */
    public function testCreateUserMissingEmailRequestKO(): void
    {
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $this->requesthelperTest->setEmailRequest('test');
        $request = $this->requesthelperTest->getRequest();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->createUserService->create($request);
    }

    /**
     * Test the creation of a user with a bad email in the request
     */
    public function testCreateUserBadEmailRequestKO(): void
    {
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $this->requesthelperTest->setEmailRequest('email', 'testBadEmail');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(['email' => 'Error email']);

        $is_create = $this->createUserService->create($request);
        $errors = $this->createUserService->validationErrors;

        $this->assertNotEmpty($errors);
        $this->assertFalse($is_create);
    }

    /**
     * Test the creation of a user with the email that does not send oneself
     */
    public function testCreateUserNotSendEmailKO(): void
    {
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $this->requesthelperTest->setEmailRequest('email', 'Test@test.test');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->mailerMock
            ->expects($this->once())
            ->method('sendTemplateEmail')
            ->willThrowException(new TransportException('Error transport mail'));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Error transport mail');

        $this->createUserService->create($request);
    }
}
