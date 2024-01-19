<?php

namespace App\Services\note;

use App\Services\security\TokenService;

class RecoveryNoteService
{
    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function recovery(): iterable
    {
        $notes = [];
        $user = $this->tokenService->getUserAuth();
        if ($user !== null) {
            $notes = $user->getNotes();
        }

        return $notes;
    }
}
