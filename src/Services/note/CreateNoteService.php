<?php

namespace App\Services\note;

use App\Entity\Note;
use App\Entity\Task;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Creation of a note
 */
class CreateNoteService
{
    public const MAX_NOTE = 25;
    public const MAX_TASK = 50;

    private EntityManagerInterface $entityManager;
    private ValidatorService $validatorService;
    private TokenService $tokenService;

    /**
     * The note entity
     */
    public ?Note $note = null;

    /**
     * The number of tasks ignored if the maximum number of task is reached for a single note
     */
    public int $ignored_task = 0;

    /**
     * Validation errors
     */
    public ?array $validationErrors = [];

    /**
     * Note Total Name
     */
    public int $total_note = 0;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorService $validatorService,
        TokenService $tokenService
    ) {
        $this->entityManager = $entityManager;
        $this->validatorService = $validatorService;
        $this->tokenService = $tokenService;
    }

    /**
     * Implementation of the creation of a note
     *
     * @param Request $request The request for the API call
     *
     * @return bool True the note has been created, false otherwise
     */
    public function create(Request $request): bool
    {
        $user = $this->tokenService->getUserAuth();
        $this->total_note = count($user->getNotes());
        $is_created = false;

        if ($this->total_note < self::MAX_NOTE) {
            $requestDatas = $this->validatorService->validateRequest($request, ['title', 'tasks']);

            $this->note = new Note();
            $this->note->setTitle($requestDatas['title']);

            $validationNote = $this->validatorService->validate($this->note);
            $validationTask = $this->createTasks($requestDatas['tasks']);
            $this->validationErrors = array_merge($validationNote, $validationTask);
            if (empty($this->validationErrors)) {
                $this->note->setUser($user);
                $this->entityManager->persist($this->note);
                $this->entityManager->flush();

                $is_created = true;
            }
        }

        return $is_created;
    }

    /**
     * Creation of a task and Recording in the note
     *
     * @param ?array $tasks Task datas
     *
     * @return array If there is no validation error an empty table, if not validation errors
     */
    private function createTasks(?array $tasks): array
    {
        $newTasks = [];
        $validationTask = [];

        foreach ($tasks as $index => $datasTasks) {
            $description_note = $datasTasks['desc'];

            $this->ignored_task++;
            if ($index + 1 <= self::MAX_TASK) {
                $this->ignored_task--;

                $task = new Task();
                $task->setDescription($description_note);

                $validationTask = $this->validatorService->validate($task);
                if (!empty($validationTask)) {
                    break;
                }

                $newTasks[] = $task;
            }
        }

        if (empty($validationTask)) {
            foreach ($newTasks as $newTask) {
                $this->note->addTask($newTask);
            }
        }

        return $validationTask;
    }
}
