<?php

namespace App\Services\user\forgotPassword;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\tools\MailerService;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CheckEmailService
{
    private MailerService $mailer;
    private UserRepository $userRepository;
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;
    private ValidatorService $validatorService;

    /**
     * Validation errors
     */
    public ?array $validationErrors = [];

    /**
     * The user linked to the email
     */
    public ?User $user = null;

    public function __construct(
        MailerService $mailer,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TokenService $tokenService,
        ValidatorService $validatorService
    ) {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->tokenService = $tokenService;
        $this->validatorService = $validatorService;
    }

    /**
     * Verification of password modification
     *
     * @param Request $request The data with the email address
     *
     * @return bool True if the email address is validated, false otherwise
     */
    public function validateEmail(Request $request): bool
    {
        $is_valid_email = false;

        $requestDatas = $this->validatorService->validateRequest($request, ['email']);
        if (empty($requestDatas['email'])) {
            $this->validationErrors['email'] = 'Ton adresse e-mail est obligatoire.';
        }

        if (empty($this->validationErrors)) {
            $this->user = $this->userRepository->findOneBy(['email' => $requestDatas['email'], 'valid' => true]);
            if (!empty($this->user)) {
                $token = $this->tokenService->createJwtToken($this->user, 'PT10M');

                $this->mailer->sendTemplateEmail([
                    'email' => $this->user->getEmail(),
                    'subject' => "Mes supers notes, modification du mot de passe",
                    'template' => 'emails/base.html.twig',
                    'context' => [
                        'title' => "Hello, petite tête",
                        'descs' => [
                            'Tu veux modifier ton mot de passe depuis "Mes supers notes" ?',
                            "Alors, pour t'aider, je t'envoie un GRAND pouvoir.",
                            "Tu sais lequel ? Celui de modifier ton mot de passe en... 3 clics.",
                            "Oui, oui, oui...",
                            "Mais tu as seulement 10 minutes pour l'utiliser.",
                            "Alors clique sur le bouton, et n'oublie pas... " .
                                "un grand pouvoir implique de grandes... blablabla..."
                        ],
                        'btn_text' => 'Modifier mon mot de passe',
                        'route_name' => 'api_forgotPassword_token',
                        'token' => $token
                    ]
                ]);

                $this->user
                    ->setToken($token)
                    ->setForgotPassword(false);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();

                $is_valid_email = true;
            }
        }

        return $is_valid_email;
    }
}
