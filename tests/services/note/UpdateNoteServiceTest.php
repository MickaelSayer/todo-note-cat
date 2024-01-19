<?php

namespace App\Tests\services\note;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use App\Services\note\UpdateNoteService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UpdateNoteServiceTest extends WebTestCase
{
    private EntityManagerInterface|MockObject $entityManagerMock;
    private ValidatorService|MockObject $validatorServiceMock;
    private TokenService|MockObject $tokenServiceMock;
    private UpdateNoteService $updateNoteService;

    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['title', 'tasks'];

    private KernelBrowser $client;
    private ContainerInterface $container;

    private User $user;
    private UserRepository $repository;

    public function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorServiceMock = $this->createMock(ValidatorService::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);

        $this->requesthelperTest = new RequestHelperTest();

        $this->updateNoteService = new UpdateNoteService(
            $this->entityManagerMock,
            $this->validatorServiceMock,
            $this->tokenServiceMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];
    }

    /**
     * Test the modification of a note with success
     */
    public function testUpdateNoteOK(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->exactly(3))
            ->method('validate')
            ->willReturn([]);

        $is_update = $this->updateNoteService->update($note_id, $request);

        $this->assertTrue($is_update);
    }

    /**
     * Test the modification of a note with an unauthentic user
     */
    public function testUpdateNoteUnauthenticUserKO(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willThrowException(new AuthenticationException('Error auth'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error auth');

        $this->updateNoteService->update($note_id, $request);
    }

    /**
     * Test the update of a note with a bad request exception
     */
    public function testUpdateNoteBadRequestExceptionKO(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest('test');
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willThrowException(new BadRequestHttpException('Error request'));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Error request');

        $this->updateNoteService->update($note_id, $request);
    }

    /**
     * Test the modification of a note with the identifier of an unknown note
     */
    public function testUpdateNoteUknownIdNoteKO(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $is_update = $this->updateNoteService->update(99, $request);

        $this->assertFalse($is_update);
    }

    /**
     * Test the modification of a note with a note without title
     */
    public function testUpdateNoteDeleteEmptyTitleNoteKO(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest('title', '');
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->exactly(3))
            ->method('validate')
            ->willReturnOnConsecutiveCalls(
                ["title" => "Le titre est obligatoire."],
                [],
                []
            );

        $is_update = $this->updateNoteService->update($note_id, $request);
        $validationErrors = $this->updateNoteService->validationErrors;

        $this->assertNotEmpty($validationErrors);
        $this->assertFalse($is_update);
    }

    /**
     * Test the modification of a task with the maximum number of task
     */
    public function testUpdateNoteDeleteMaxTasksOK(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest('tasks', 0, 55);
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, $this->requestExpected)
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->exactly(51))
            ->method('validate')
            ->willReturn([]);

        $is_update = $this->updateNoteService->update($note_id, $request);
        $ignored_task = $this->updateNoteService->ignored_task;

        $this->assertSame(5, $ignored_task);
        $this->assertTrue($is_update);
    }

    /**
     * Test the update of a note with validation of the 1st description of an empty stain
     */
    public function testCreateNoteValidationFirstTasksDescEmptyKO(): void
    {
        $note_id = $this->user->getNotes()[0]->getId();

        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest('tasks', 2, 5);
        $request = $this->requesthelperTest->getRequest();
        $requestContent = $this->requesthelperTest->getRequestContent();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $this->validatorServiceMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request, ['title', 'tasks'])
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturnOnConsecutiveCalls(
                [],
                ['tasks' => 'La description est obligatoire']
            );

        $is_created = $this->updateNoteService->update($note_id, $request);
        $validationErrors = $this->updateNoteService->validationErrors;

        $this->assertNotEmpty($validationErrors);
        $this->assertFalse($is_created);
    }
}
