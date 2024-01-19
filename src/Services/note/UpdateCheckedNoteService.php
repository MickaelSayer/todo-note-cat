<?php

namespace App\Services\note;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\security\TokenService;

class UpdateCheckedNoteService
{
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;

    public ?Task $task = null;

    public function __construct(
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;
    }

    public function checked(int $id): bool
    {
        $is_checked = false;
        $user = $this->tokenService->getUserAuth();

        if ($user !== null) {
            $notes = $user->getNotes();
            if (!empty($notes)) {
                foreach ($notes as $note) {
                    $tasks = $note->getTasks();

                    foreach ($tasks as $task) {
                        if ($task->getId() === $id) {
                            $this->task = $task;

                            break;
                        }
                    }
                }

                if (!empty($this->task) && $this->task->getId() === $id) {
                    $this->task->setChecked(!$this->task->isChecked());
                    $this->entityManager->persist($this->task);
                    $this->entityManager->flush();

                    $is_checked = true;
                }
            }
        }

        return $is_checked;
    }
}
