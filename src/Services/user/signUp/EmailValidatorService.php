<?php

namespace App\Services\user\signUp;

use App\Entity\User;
use App\Services\security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EmailValidatorService
{
    private EntityManagerInterface $entityManager;
    private TokenService $tokenService;

    private ?User $user = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenService $tokenService
    ) {
        $this->entityManager = $entityManager;
        $this->tokenService = $tokenService;
    }

    /**
     * Check the validity of the registration email
     *
     * @param Request $request
     *
     * @return bool True If the email is checked, false otherwise
     */
    public function validateEmail(Request $request): bool
    {
        $is_validate = false;
        $token = $request->request->get('token', null);
        if ($token !== null) {
            $this->user = $this->tokenService->decodeJwtTokenUser($token);
            if ($this->user !== null && !$this->user->isValid()) {
                $this->user
                    ->setToken('')
                    ->setValid(true);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();

                $is_validate = true;
            }
        }

        return $is_validate;
    }
}
