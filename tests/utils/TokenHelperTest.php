<?php

namespace App\Tests\utils;

use DateTime;
use DateInterval;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenHelperTest
{
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Creation of a token
     *
     * @param bool $is_expired True adds a token with an expiration date lower than the date of creation
     * False The expiration date will be supplied to the creation date
     */
    public function setTokenUser(User $user, bool $is_expired = false): void
    {
        $expiredFunction = 'add';
        if ($is_expired) {
            $expiredFunction = 'sub';
        }
        $currentDateTime = new DateTime();
        $expirationDateTime = clone $currentDateTime;
        $expirationDateTime = $expirationDateTime->{$expiredFunction}(new DateInterval('PT10S'));

        $token = $this->jwtManager->createFromPayload($user, [
            "iat" => $currentDateTime->getTimestamp(),
            "exp" => $expirationDateTime->getTimestamp()
        ]);

        $user->setToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
