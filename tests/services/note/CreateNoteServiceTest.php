<?php

namespace App\Tests\services\note;

use App\Entity\User;
use App\Tests\utils\NoteHelperTest;
use App\Tests\utils\RequestHelperTest;
use App\Services\security\TokenService;
use App\Services\note\CreateNoteService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CreateNoteServiceTest extends WebTestCase
{
    private EntityManagerInterface|MockObject $entityManagerMock;
    private ValidatorService|MockObject $validatorServiceMock;
    private TokenService|MockObject $tokenServiceMock;
    private CreateNoteService $createNoteService;

    private NoteHelperTest $noteHelperTest;
    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['title', 'tasks'];

    private KernelBrowser $client;
    private ContainerInterface $container;

    private User $user;

    public function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorServiceMock = $this->createMock(ValidatorService::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);

        $this->requesthelperTest = new RequestHelperTest();
        $this->noteHelperTest = new NoteHelperTest();

        $this->createNoteService = new CreateNoteService(
            $this->entityManagerMock,
            $this->validatorServiceMock,
            $this->tokenServiceMock
        );

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];
    }

    /**
     * Test the creation of a note with success
     */
    public function testCreateNoteOK(): void
    {
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

        $is_created = $this->createNoteService->create($request);

        $this->assertTrue($is_created);
    }

    /**
     * Test the creation of a note with too many tasks creates
     */
    public function testCreateNoteMaxTasksOK(): void
    {
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

        $is_created = $this->createNoteService->create($request);
        $ignored_task = $this->createNoteService->ignored_task;

        $this->assertSame(5, $ignored_task);
        $this->assertTrue($is_created);
    }

    /**
     * Test the creation of a note with a authentication exception
     */
    public function testCreateNoteAuthenticationExceptionKO(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willThrowException(new AuthenticationException('Error auth'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error auth');

        $this->createNoteService->create($request);
    }

    /**
     * Test the creation of a note with a user with too much note
     */
    public function testCreateNoteMaxNoteKO(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $this->noteHelperTest->addUserNote($this->user, 30);

        $this->tokenServiceMock
            ->expects($this->once())
            ->method('getUserAuth')
            ->willReturn($this->user);

        $is_created = $this->createNoteService->create($request);
        $total_note = $this->createNoteService->total_note;
        $note = $this->createNoteService->note;

        $this->assertEmpty($note);
        $this->assertSame(30, $total_note);
        $this->assertFalse($is_created);
    }

    /**
     * Test the creation of a note with a bad request exception
     */
    public function testCreateNoteBadRequestExceptionKO(): void
    {
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

        $is_created = $this->createNoteService->create($request);

        $this->assertTrue($is_created);
    }

    /**
     * Test the creation of a note with a validation error title note
     */
    public function testCreateNoteValidationTitleNoteKO(): void
    {
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
            ->with($request, ['title', 'tasks'])
            ->willReturn($requestContent);

        $this->validatorServiceMock
            ->expects($this->exactly(3))
            ->method('validate')
            ->willReturnOnConsecutiveCalls(
                ["title" => "Le titre est obligatoire."],
                [],
                []
            );

        $is_created = $this->createNoteService->create($request);
        $validationErrors = $this->createNoteService->validationErrors;

        $this->assertNotEmpty($validationErrors);
        $this->assertFalse($is_created);
    }

    /**
     * Test the creation of a note with validation of the 1st description of an empty stain
     */
    public function testCreateNoteValidationFirstTasksDescEmptyKO(): void
    {
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

        $is_created = $this->createNoteService->create($request);
        $validationErrors = $this->createNoteService->validationErrors;

        $this->assertNotEmpty($validationErrors);
        $this->assertFalse($is_created);
    }
}
