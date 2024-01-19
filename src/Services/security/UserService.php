<?php

namespace App\Services\security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Check that the user exists using an email and a password
     *
     * @param string $email
     * @param string $password
     *
     * @return ?User The user if it is valid, null if not
     */
    public function isValidUser(string $email, string $password): ?User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (empty($user) || !$this->passwordHasher->isPasswordValid($user, $password)) {
            $user = null;
        }

        return $user;
    }
}
