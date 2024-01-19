<?php

namespace App\Controller\user\signUp;

use Exception;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\user\signUp\CreateUserService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class CreateAccountController extends AbstractController
{
    private CreateUserService $createUser;
    private CreateResponseService $createResponse;

    public function __construct(
        CreateUserService $createUser,
        CreateResponseService $createResponse
    ) {
        $this->createUser = $createUser;
        $this->createResponse = $createResponse;
    }

    /**
     *
     * Check the account creation form
     *
     * @OA\Response(
     *     response=201,
     *     description="The account was created and an email was sent to validate the email address",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="201"),
     *         @OA\Property(property="success", type="string", example="Example message success")
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Gives the validation errors of the email or password",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="400"),
     *         @OA\Property(
     *              property="validation",
     *              type="object",
     *              ref="#/components/schemas/ValidationLogin"
     *         )
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
     *         @OA\Property(property="email", type="string", example="Test@test.test"),
     *         @OA\Property(property="password", type="string", example="A1@aaaaa")
     *     )
     * )
     *
     * @OA\Post(
     *     path="/api/user/signUp",
     *     description="This endpoint sends an email. Requires a local SMTP server. (Ex : MailDev)",
     *     security={}
     * )
     *
     * @OA\Tag(name="User - SignUp")
     *
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/user/signUp", name:"api_signUp", methods:"POST")]
    public function create(Request $request): JsonResponse
    {
        try {
            if ($this->createUser->create($request)) {
                $this->setResponseSuccess();
            } else {
                $this->setResponseValidation();
            }
        } catch (Exception $exception) {
            $this->setResponseCriticalException($exception);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $this->createResponse->statusCode(Response::HTTP_CREATED);
        $this->createResponse->success(
            "Trop bien, l'e-mail de confirmation a été envoyé. Tu as 1 heure pour valider ton adresse e-mail"
        );
    }

    private function setResponseValidation(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($this->createUser->validationErrors);
    }

    private function setResponseCriticalException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
