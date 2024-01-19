<?php

namespace App\Services\user\signUp;

use App\Entity\User;
use App\Services\tools\MailerService;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserService extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorService $validatorService;
    private EntityManagerInterface $entityManager;
    private MailerService $mailer;
    private TokenService $tokenService;

    public array $validationErrors = [];

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ValidatorService $validatorService,
        EntityManagerInterface $entityManager,
        MailerService $mailer,
        TokenService $tokenService
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->validatorService = $validatorService;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->tokenService = $tokenService;
    }

    /**
     * Add the user in a database
     *
     * @param Request $request Announcement data for a UP sign
     *
     * @return ?array True if the user and create, false otherwise
     */
    public function create(Request $request): ?bool
    {
        $is_create = false;
        $requestDatas = $this->validatorService->validateRequest($request, ['email', 'password']);

        $user = new User();
        $user
            ->setPassword($requestDatas['password'])
            ->setEmail($requestDatas['email']);

        $this->validationErrors = $this->validatorService->validate($user);
        if (empty($this->validationErrors)) {
            $token = $this->tokenService->createJwtToken($user);

            $this->mailer->sendTemplateEmail([
                'email' => $user->getEmail(),
                'subject' => "Mes supers notes, vérification d'e-mail.",
                'template' => 'emails/base.html.twig',
                'context' => [
                    'title' => 'Me revoilà, chef',
                    'descs' => [
                        'Tu viens de crée un compte sur "Mes supers notes"',
                        "Alors, pour te remercier, je t'envoie un super pouvoir.",
                        "Tu sais lequel ? Celui de vérifier ton adresse e-mail en 1 clic",
                        "MAIS attention, il est disponible seulement pendant 1 heure",
                        "Extraordinaire, non ? (non)",
                        "Clique sur le bouton, et n'oublie pas... un grand
                            pouvoir implique de grandes responsabilités."
                    ],
                    'btn_text' => 'Vérifier mon adresse e-mail',
                    'route_name' => 'api_signUp_email',
                    'token' => $token
                ]
            ]);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $requestDatas['password']);
            $user
                ->setPassword($hashedPassword)
                ->setToken($token);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $is_create = true;
        }

        return $is_create;
    }
}
