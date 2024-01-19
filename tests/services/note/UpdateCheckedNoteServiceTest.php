<?php

namespace App\Tests\services\note;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\note\UpdateCheckedNoteService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UpdateCheckedNoteServiceTest extends WebTestCase
{
    private TokenService|MockObject $tokenServiceMock;
    private EntityManagerInterface|MockObject $entityManagerMock;
    private UpdateCheckedNoteService $updateCheckedNoteService;

    private KernelBrowser $client;
    private ContainerInterface $container;
    private UserRepository $repository;

    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->updateCheckedNoteService = new UpdateCheckedNoteService(
            $this->tokenServiceMock,
            $this->entityManagerMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];
    }

    /**
     * checked a task with success
     */
    public function testCheckedTaskOK(): void
    {
        $note = $this->user->getNotes()[0];
        $task_id = $note->getTasks()[0]->getId();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $is_checked = $this->updateCheckedNoteService->checked($task_id);

        $this->assertTrue($is_checked);
    }

    /**
     * checked a task with invalid token
     */
    public function testCheckedTaskInvalidTokenKO(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willThrowException(new AuthenticationException('Error auth'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error auth');

        $this->updateCheckedNoteService->checked(999);
    }

    /**
     * checked a task with without task
     */
    public function testCheckedTaskWithoutTaskKO(): void
    {
        $user = $this->repository->findAll()[2];

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($user);

        $is_checked = $this->updateCheckedNoteService->checked(1);

        $this->assertFalse($is_checked);
    }
}
