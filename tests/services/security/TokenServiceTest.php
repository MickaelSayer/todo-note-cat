<?php

namespace App\Tests\services\security;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\TokenHelperTest;
use App\Services\security\TokenService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenServiceTest extends WebTestCase
{
    private TokenStorageInterface|MockObject $tokenStorageMock;
    private JWTTokenManagerInterface|MockObject $jwtManagerMock;
    private UserRepository|MockObject $userRepositoryMock;
    private TokenInterface|MockObject $tokenInterfaceMock;

    private TokenHelperTest $tokenHelper;
    private TokenService $tokenService;

    private KernelBrowser $client;
    private ContainerInterface $container;
    private User $user;

    public function setUp(): void
    {
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->jwtManagerMock = $this->createMock(JWTTokenManagerInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->tokenInterfaceMock = $this->createMock(TokenInterface::class);

        $this->tokenService = new TokenService(
            $this->tokenStorageMock,
            $this->jwtManagerMock,
            $this->userRepositoryMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test the user recovery successfully authenticated
     */
    public function testIsValidTokenStorageOK(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->tokenStorageMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->tokenInterfaceMock);

        $this->tokenInterfaceMock
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($this->user);

        $user = $this->tokenService->getUserAuth();

        $this->assertNotNull($user);
        $this->assertEquals($this->user, $user);
    }

    /**
     * Test user recovery, no authenticated user
     */
    public function testIsValidTokenStorageExpirateKO(): void
    {
        $this->tokenStorageMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        try {
            $this->tokenService->getUserAuth();
        } catch (AuthenticationException $exception) {
            $this->assertSame("Unable to recover the user in the token storage", $exception->getMessage());
        }
    }

    /**
     * Add a token to the user with success
     */
    public function testAddJwtTokenUserOK(): void
    {
        $currentDateTime = new DateTime();
        $expirationDateTime = clone $currentDateTime;
        $expirationDateTime = $expirationDateTime->add(new DateInterval('PT1H'));
        $expirate = [
            "iat" => $currentDateTime->getTimestamp(),
            "exp" => $expirationDateTime->getTimestamp()
        ];
        $token = 'A1B2C3D4';

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('createFromPayload')
            ->with($this->user, $expirate)
            ->willReturn($token);

        $token = $this->tokenService->createJwtToken($this->user, 'PT1H');

        $this->assertNotEmpty($token);
    }

    /**
     * Test the verification of the token saved in the user with success
     */
    public function testDecodeJwtTokenUserOK(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('parse')
            ->with($this->user->getToken())
            ->willReturn([
                'username' => $this->user->getEmail()
            ]);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $this->user->getEmail()])
            ->willReturn($this->user);

        $user = $this->tokenService->decodeJwtTokenUser($this->user->getToken());

        $this->assertNotNull($user);
    }

    /**
     * Test the verification of the token with an expired token
     */
    public function testDecodeJwtTokenUserExpiredKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('parse')
            ->with($this->user->getToken())
            ->willReturn([]);

        $user = $this->tokenService->decodeJwtTokenUser($this->user->getToken());

        $this->assertNull($user);
    }

    /**
     * Test the verification of the token with an expired token
     */
    public function testDecodeJwtTokenUserInvalidKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('parse')
            ->with('A1B2C3D4E5')
            ->wilLReturn([]);

        $user = $this->tokenService->decodeJwtTokenUser('A1B2C3D4E5');

        $this->assertNull($user);
    }

    /**
     * Test the verification of the token saved in the user with bad email
     */
    public function testDecodeJwtTokenUserBadEmailKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('parse')
            ->with($this->user->getToken())
            ->willReturn([
                'username' => 'test@test.test'
            ]);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@test.test'])
            ->willReturn(null);

        $user = $this->tokenService->decodeJwtTokenUser($this->user->getToken());

        $this->assertNull($user);
    }
}
