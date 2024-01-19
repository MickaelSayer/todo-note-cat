<?php

namespace App\Services\security;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenService
{
    private TokenStorageInterface $tokenStorage;
    private JWTTokenManagerInterface $jwtManager;
    private UserRepository $userRepository;

    private ?TokenInterface $tokenInterface = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        UserRepository $userRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Check the validity of the token
     *
     * @return User The connected user
     *
     * @throws  AuthenticationException
     */
    public function getUserAuth(): ?User
    {
        $this->tokenInterface = $this->tokenStorage->getToken();
        if (empty($this->tokenInterface) || !$this->tokenInterface->getUser() instanceof UserInterface) {
            throw new AuthenticationException("Unable to recover the user in the token storage");
        }

        /**
         * @var User
         */
        $user = $this->tokenInterface->getUser();

        return $user;
    }

    /**
     * Create a jwt token
     *
     * @param User $user
     * @param string $duration The expiration duration of the token
     *
     * @return string The token
     */
    public function createJwtToken(User $user, string $duration = 'PT1H'): string
    {
        $currentDateTime = new DateTime();
        $expirationDateTime = clone $currentDateTime;
        $expirationDateTime = $expirationDateTime->add(new DateInterval($duration));
        $token = $this->jwtManager->createFromPayload($user, [
            "iat" => $currentDateTime->getTimestamp(),
            "exp" => $expirationDateTime->getTimestamp()
        ]);

        return $token;
    }

    /**
     * Check the validity of the JWT token
     *
     * @param ?string $token the token JWR
     *
     * @return ?User Return the user if he is found in the token, otherwise null
     *
     * @throws BadRequestHttpException
     */
    public function decodeJwtTokenUser(?string $token): ?User
    {
        if ($token === null) {
            throw new BadRequestHttpException("The token should not be null to be able to decorate it");
        }

        $user = null;

        $userToken = $this->jwtManager->parse($token);
        if (!empty($userToken) && isset($userToken['username'])) {
            $user = $this->userRepository->findOneBy(['email' => $userToken['username']]);
        }

        return $user;
    }
}
