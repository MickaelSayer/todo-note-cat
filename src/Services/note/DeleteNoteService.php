<?php

namespace App\Services\note;

use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\security\TokenService;

class DeleteNoteService
{
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;
    private NoteRepository $noteRepository;

    public function __construct(
        TokenService $tokenService,
        EntityManagerInterface $entityManager,
        NoteRepository $noteRepository
    ) {
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;
        $this->noteRepository = $noteRepository;
    }

    public function delete(int $id): bool
    {
        $is_delete = false;
        $user = $this->tokenService->getUserAuth();
        if ($user !== null) {
            $note = $this->noteRepository->find($id);
            if (!empty($note)) {
                $user->removeNote($note);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $is_delete = true;
            }
        }

        return $is_delete;
    }
}
