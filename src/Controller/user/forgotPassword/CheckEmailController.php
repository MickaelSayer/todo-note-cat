<?php

namespace App\Controller\user\forgotPassword;

use Exception;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\user\forgotPassword\CheckEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class CheckEmailController extends AbstractController
{
    private CreateResponseService $createResponse;
    private CheckEmailService $checkEmailService;

    public function __construct(
        CreateResponseService $createResponse,
        CheckEmailService $checkEmailService,
    ) {
        $this->createResponse = $createResponse;
        $this->checkEmailService = $checkEmailService;
    }

    /**
     *
     * Check that the user's email address exists for changing the password (Etape 1)
     *
     * @OA\Response(
     *     response=200,
     *     description="The email has been verified with success",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200"),
     *         @OA\Property(property="token", type="string", example="The token"),
     *         @OA\Property(property="success", type="string", example="Message success"),
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="An entitled validation error or missing data",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="400"),
     *         @OA\Property(
     *              property="validation",
     *              type="object", ref="#/components/schemas/ValidationForgotPasswordEmail"
     *         ),
     *         @OA\Property(property="critical_error", type="string", example="Message critical error")
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
     *         @OA\Property(property="email", type="string", example="Example@email.com")
     *     ),
     * )
     *
     * @OA\Post(
     *     path="/api/user/forgotPassword/email",
     *     description="This endpoint sends an email. Requires a local SMTP server. (Ex : MailDev)",
     *     security={}
     * )
     *
     * @OA\Tag(name="User - Forgot Password")
     *
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/user/forgotPassword/email", name:"api_forgotPassword_email", methods:"POST")]
    public function check(Request $request): JsonResponse
    {
        try {
            if ($this->checkEmailService->validateEmail($request)) {
                $this->setResponseSuccess();
            } else {
                if (!empty($this->checkEmailService->validationErrors)) {
                    $this->setResponseValidation();
                } else {
                    $this->setResponseCriticalError();
                }
            }
        } catch (Exception $exception) {
            $this->setResponseException($exception);
        }

        $response = $this->createResponse->getResponse();
        return  new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $this->createResponse->statusCode(Response::HTTP_OK);
        $this->createResponse->token($this->checkEmailService->user->getToken());
        $this->createResponse->success(
            "J'ai envoyé l'email pour réinitialiser ton mot de passe."
        );
    }

    private function setResponseValidation(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($this->checkEmailService->validationErrors);
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->criticalError('Je ne trouve pas ton adresse e-mail, desolé.');
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
