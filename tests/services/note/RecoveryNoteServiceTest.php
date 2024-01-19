<?php

namespace App\Tests\services\note;

use App\Entity\User;
use App\Services\security\TokenService;
use App\Services\note\RecoveryNoteService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RecoveryNoteServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private RecoveryNoteService $recoveryNoteService;

    private KernelBrowser $client;
    private ContainerInterface $container;

    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->recoveryNoteService = new RecoveryNoteService($this->tokenServiceMock);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $userRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $userRepository->findAll()[0];
    }

    /**
     * Test the recovery of all notes
     */
    public function testRecoveryNoteOK(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $notes = $this->recoveryNoteService->recovery();

        $this->assertNotEmpty($notes);
    }

    /**
     * Note recovery test without invalid token
     */
    public function testRecoveryNoteInvalidTokenKO(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willThrowException(new AuthenticationException('Error auth'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error auth');

        $this->recoveryNoteService->recovery();
    }
}
