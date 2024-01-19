<?php

namespace App\Tests\services\note;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use App\Services\security\TokenService;
use App\Services\note\DeleteNoteService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DeleteNoteServiceTest extends WebTestCase
{
    private EntityManagerInterface|MockObject $entityManagerMock;
    private NoteRepository|MockObject $noteRepositoryMock;
    private TokenService|MockObject $tokenServiceMock;
    private DeleteNoteService $deleteNoteService;

    private KernelBrowser $client;
    private ContainerInterface $container;
    private Note $note;
    private User $user;

    public function setUp(): void
    {
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->noteRepositoryMock = $this->createMock(NoteRepository::class);

        $this->deleteNoteService = new DeleteNoteService(
            $this->tokenServiceMock,
            $this->entityManagerMock,
            $this->noteRepositoryMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $userRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $userRepository->findAll()[0];
        $this->note = $this->user->getNotes()[0];
    }

    /**
     * Test the deletion of a successful note
     */
    public function testDeleteNoteOK(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->noteRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->note->getId())
            ->willReturn($this->note);

        $is_delete = $this->deleteNoteService->delete($this->note->getId());

        $this->assertTrue($is_delete);
    }

    /**
     * Test the deletion of a note with a bad token
     */
    public function testDeleteNoteInvalidTokenKO(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willThrowException(new AuthenticationException('Error auth'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error auth');

        $this->deleteNoteService->delete($this->note->getId());
    }

    /**
     * Test the deletion of a note with a bad token
     */
    public function testDeleteNoteInvalidNoteKO(): void
    {
        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->noteRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $is_delete = $this->deleteNoteService->delete(999);

        $this->assertFalse($is_delete);
    }
}
