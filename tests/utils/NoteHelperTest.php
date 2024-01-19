<?php

namespace App\Tests\utils;

use App\Entity\Note;
use App\Entity\User;

class NoteHelperTest
{
    public array $tasks_created = [];

    /**
     * Add notes to a user
     *
     * @param User $user
     * @param int $total_note The total number of note to create
     */
    public function addUserNote(User $user, int $total_note = 55): void
    {
        for ($i = count($user->getNotes()); $i < $total_note; $i++) {
            $note = new Note();
            $note->setTitle($i);

            $user->addNote($note);
        }
    }
}
