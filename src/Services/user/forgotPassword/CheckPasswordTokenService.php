<?php

namespace App\Services\user\forgotPassword;

use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CheckPasswordTokenService
{
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorService $validatorService;

    public ?array $validationErrors = [];

    public function __construct(
        TokenService $tokenService,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorService $validatorService
    ) {
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validatorService = $validatorService;
    }

    /**
     * Checking the token + Password modification
     *
     * @param Request $request
     *
     * @return bool True if the token is valid, and modification of the password, false otherwise
     */
    public function checkPasswordToken(Request $request): bool
    {
        $requestDatas = $this->validatorService->validateRequest($request, ['password']);
        $is_valid = false;

        $user = $this->tokenService->getUserAuth();
        if ($user !== null && $user->isValid()) {
            if (!$user->isForgotPassword()) {
                throw new BadRequestHttpException("The user is not allowed to modify their password");
            }
            $user->setPassword($requestDatas['password']);

            $this->validationErrors = $this->validatorService->validate($user);
            if (empty($this->validationErrors)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $requestDatas['password']);
                $user
                    ->setPassword($hashedPassword)
                    ->setToken('')
                    ->setForgotPassword(false);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $is_valid = true;
            }
        }

        return $is_valid;
    }
}
