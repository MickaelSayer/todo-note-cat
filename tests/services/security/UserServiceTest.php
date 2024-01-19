<?php

namespace App\Tests\services\security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\security\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends WebTestCase
{
    private UserRepository|MockObject $userRepositoryMock;
    private UserPasswordHasherInterface|MockObject $passwordHasherMock;

    private UserService $userService;

    private KernelBrowser $client;
    private ContainerInterface $container;
    private User $user;

    public function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->userRepositoryMock,
            $this->passwordHasherMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];
    }

    /**
     * Test the verification of the password and the email of a user with success
     */
    public function testIsValidUserOK(): void
    {
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $this->user->getEmail()])
            ->willReturn($this->user);

        $this->passwordHasherMock
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, '123456789')
            ->willReturn(true);

        $user = $this->userService->isValidUser($this->user->getEmail(), '123456789');

        $this->assertNotNull($user);
    }

    /**
     * Test the verification of the password and the email of a user with invalid email
     */
    public function testIsValidUserEmailInvalidKO(): void
    {
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@test.test'])
            ->willReturn(null);

        $this->passwordHasherMock
            ->expects($this->never())
            ->method('isPasswordValid');

        $user = $this->userService->isValidUser('test@test.test', '123456789');

        $this->assertNull($user);
    }

    /**
     * Test the verification of the password and the email of a user with invalid password
     */
    public function testIsValidUserPasswordInvalidKO(): void
    {
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $this->user->getEmail()])
            ->willReturn($this->user);

        $this->passwordHasherMock
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, '159357')
            ->willReturn(false);

        $user = $this->userService->isValidUser($this->user->getEmail(), '159357');

        $this->assertNull($user);
    }
}
