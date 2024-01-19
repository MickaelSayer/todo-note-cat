<?php

namespace App\Controller\user\login;

use Exception;
use App\Services\security\UserService;
use App\Services\tools\ValidatorService;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class CheckFormController extends AbstractController
{
    private UserService $userService;
    private CreateResponseService $createResponse;
    private ValidatorService $validatorService;

    public function __construct(
        UserService $userService,
        CreateResponseService $createResponse,
        ValidatorService $validatorService
    ) {
        $this->userService = $userService;
        $this->createResponse = $createResponse;
        $this->validatorService = $validatorService;
    }

    /**
     *
     * Check data from the connection form
     *
     * @OA\Response(
     *     response=204,
     *     description="The connection form has been correctly completed"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Connection form data is not good (Email not validated or bad connection data)",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="401"),
     *         @OA\Property(
     *              property="validation",
     *              type="object",
     *              ref="#/components/schemas/ValidationLoginEmailInvalid"
     *         ),
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Connection form data includes validation errors",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="400"),
     *         @OA\Property(property="validation", type="object", ref="#/components/schemas/ValidationLogin"),
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="An exception has been lifted",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="500"),
     *         @OA\Property(property="exception", type="string", example="Exception message")
     *     )
     * )
     *
     * @OA\RequestBody(
     *     required=true,
     *     description="Request body",
     *     @OA\JsonContent(
     *         @OA\Property(property="email", type="string", example="Example@email.com"),
     *         @OA\Property(property="password", type="string", example="ExamplePassword@2"),
     *     ),
     * )
     *
     * @OA\Post(
     *     path="/api/user/login/validation",
     *     security={}
     * )
     *
     * @OA\Tag(name="User - Login")
     *
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/user/login/validation", name:"api_login_validation", methods:"POST")]
    public function check(Request $request): JsonResponse
    {
        try {
            $requestDatas = $this->validatorService->validateRequest($request, ['email', 'password']);
            if (empty($requestDatas['email']) || empty($requestDatas['password'])) {
                $this->setResponseValidation($requestDatas['email'], $requestDatas['password']);
            } else {
                $user = $this->userService->isValidUser($requestDatas['email'], $requestDatas['password']);
                if ($user !== null) {
                    if (!$user->isValid()) {
                        $this->setResponseEmailIsInvalid();
                    } else {
                        $this->createResponse->statusCode(Response::HTTP_NO_CONTENT);
                    }
                } else {
                    $this->setResponseValidationAuth();
                }
            }
        } catch (Exception $e) {
            $this->setResponseException($e);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseEmailIsInvalid(): void
    {
        $this->createResponse->validation([
            "valid" => "Alala, je ne peux pas te laisser rentrer, tu n'as pas validé ton adresse e-mail."
        ]);
        $this->createResponse->statusCode(Response::HTTP_UNAUTHORIZED);
    }

    private function setResponseValidationAuth(): void
    {
        $this->createResponse->validation([
            "email" => "Ton adresse e-mail ou ton mot de passe n'est pas correct."
        ]);
        $this->createResponse->statusCode(Response::HTTP_UNAUTHORIZED);
    }

    private function setResponseValidation(string $email, string $password): void
    {
        $errors = [];

        if (empty($email)) {
            $errors['email'] = "Hein ? J'ai du mal à... lire ton adresse e-mail.";
        }

        if (empty($password)) {
            $errors['password'] = "Mot de passe !!! ...stp";
        }

        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($errors);
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
