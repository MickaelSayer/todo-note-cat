<?php

namespace App\Services\note;

use App\Entity\Note;
use App\Entity\Task;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UpdateNoteService
{
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
    public ?int $ignored_task = 0;

    /**
     * Validation errors
     */
    public ?array $validationErrors = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorService $validatorService,
        TokenService $tokenService
    ) {
        $this->entityManager = $entityManager;
        $this->validatorService = $validatorService;
        $this->tokenService = $tokenService;
    }

    public function update(int $id, Request $request): bool
    {
        $user = $this->tokenService->getUserAuth();
        $is_update = false;

        $notes = $user->getNotes();
        $note = $notes->filter(function (Note $note) use ($id) {
            return $note->getId() === $id ? $note : null;
        });
        $this->note = $note->first() ? $note->first() : null;

        if (!empty($this->note)) {
            $requestDatas = $this->validatorService->validateRequest($request, ['title', 'tasks']);

            $this->note->setTitle($requestDatas['title']);
            $validationNote = $this->validatorService->validate($this->note);
            $validationTask = $this->createTasks($requestDatas['tasks']);

            $this->validationErrors = array_merge($validationNote, $validationTask);
            if (empty($this->validationErrors)) {
                $this->entityManager->persist($this->note);
                $this->entityManager->flush();

                $is_update = true;
            }
        }

        return $is_update;
    }

    /**
     * Deletion of old tasks
     */
    private function deleteTasks(): void
    {
        foreach ($this->note->getTasks() as $task) {
            $this->note->removeTask($task);
        }
        $this->note->getTasks()->clear();
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
        $this->deleteTasks();

        $newTasks = [];
        $validationTask = [];

        foreach ($tasks as $index => $datasTasks) {
            $description_note = $datasTasks['desc'];
            $is_checked = !isset($datasTasks['checked']) ? false : $datasTasks['checked'];

            $this->ignored_task++;
            if ($index + 1 <= self::MAX_TASK) {
                $this->ignored_task--;

                $task = new Task();
                $task
                    ->setDescription($description_note)
                    ->setChecked($is_checked);

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
