<?php

namespace App\Services\user\forgotPassword;

use App\Services\security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CheckTokenService
{
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;
    }

    /**
     * Validation of the token sent by email before changing the password
     *
     * @param Request $request
     *
     * @return bool True if the token is valid, false otherwise
     */
    public function validateToken(Request $request): bool
    {
        $is_valid = false;
        $token = $request->request->get('token', null);
        if ($token !== null) {
            $user = $this->tokenService->decodeJwtTokenUser($token);
            if ($user !== null) {
                $user->setForgotPassword(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $is_valid = true;
            }
        }

        return $is_valid;
    }
}
