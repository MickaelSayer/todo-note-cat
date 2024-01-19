<?php

namespace App\Tests\services\security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\TokenHelperTest;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Services\user\forgotPassword\CheckPasswordTokenService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

class CheckPasswordTokenServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;
    private UserPasswordHasherInterface|MockObject $passwordHasherMock;
    private ValidatorService|MockObject $validatorServiceMock;

    private TokenHelperTest $tokenHelper;
    private RequestHelperTest $requesthelperTest;
    private CheckPasswordTokenService $checkPasswordTokenService;
    private array $requestExpected = ['password'];

    private KernelBrowser $client;
    private ContainerInterface $container;
    private UserRepository $repository;
    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $this->validatorServiceMock = $this->createMock(ValidatorService::class);

        $this->requesthelperTest = new RequestHelperTest();

        $this->checkPasswordTokenService = new CheckPasswordTokenService(
            $this->tokenServiceMock,
            $this->entityManagerMock,
            $this->passwordHasherMock,
            $this->validatorServiceMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test The password modification with success
     */
    public function testCheckPasswordTokenOK(): void
    {
        $this->user->setForgotPassword(true);
        $this->tokenHelper->setTokenUser($this->user);
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->with()
            ->willReturn($this->user);

        $is_valid = $this->checkPasswordTokenService->checkPasswordToken($request);

        $this->assertTrue($is_valid);
    }

    /**
     * Test The password modification with the missing password in the request
     */
    public function testCheckPasswordTokenMissingPasswordRequestKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setPasswordRequest('test');
        $request = $this->requesthelperTest->getRequest();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->checkPasswordTokenService->checkPasswordToken($request);
    }

    /**
     * Test The password modification with the missing token in the request
     */
    public function testCheckPasswordTokenMissingTokenRequestKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setPasswordRequest('password');
        $request = $this->requesthelperTest->getRequest();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->checkPasswordTokenService->checkPasswordToken($request);
    }

    /**
     * Test The password modification with the bad token in the request
     */
    public function testCheckPasswordTokenBadTokenRequestKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->with()
            ->willReturn(null);

        $is_valid = $this->checkPasswordTokenService->checkPasswordToken($request);

        $this->assertFalse($is_valid);
    }

    /**
     * Test The password modification with the expirate token in the request
     */
    public function testCheckPasswordTokenExpirateTokenRequestKO(): void
    {
        $this->requesthelperTest->setPasswordRequest('password', 'A1@aaaaa');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->with()
            ->willThrowException(new JWTDecodeFailureException('expired_token', 'Error token expirate'));

        $this->expectException(JWTDecodeFailureException::class);
        $this->expectExceptionMessage('Error token expirate');

        $is_valid = $this->checkPasswordTokenService->checkPasswordToken($request);
    }

    /**
     * Test The password modification with the bad password in the request
     */
    public function testCheckPasswordTokenBadPasswordRequestKO(): void
    {
        $this->user->setForgotPassword(true);
        $this->tokenHelper->setTokenUser($this->user);

        $this->requesthelperTest->setPasswordRequest('password', '123456789');
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->with()
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(['password' => 'Le password ne correspond pas']);

        $is_valid = $this->checkPasswordTokenService->checkPasswordToken($request);
        $errors = $this->checkPasswordTokenService->validationErrors;

        $this->assertNotEmpty($errors);
        $this->assertFalse($is_valid);
    }
}
