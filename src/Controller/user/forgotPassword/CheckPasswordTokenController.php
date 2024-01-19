<?php

namespace App\Controller\user\forgotPassword;

use Exception;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\user\forgotPassword\CheckPasswordTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CheckPasswordTokenController extends AbstractController
{
    private CheckPasswordTokenService $checkPasswordTokenService;
    private CreateResponseService $createResponse;

    public function __construct(
        CheckPasswordTokenService $checkPasswordTokenService,
        CreateResponseService $createResponse
    ) {
        $this->checkPasswordTokenService = $checkPasswordTokenService;
        $this->createResponse = $createResponse;
    }

    /**
     *
     * Check the token and password when changing the password (Etape 3)
     *
     * @OA\Response(
     *     response=200,
     *     description="The password modification was made",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200"),
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
     *              type="object", ref="#/components/schemas/ValidationForgotPasswordPassword"
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
     *     description="The new password",
     *     @OA\JsonContent(
     *         @OA\Property(property="password", type="string", example="ExamplePassword@2")
     *     )
     * )
     *
     * @Security(name="BearerForgotPassword"),
     * @OA\Tag(name="User - Forgot Password")
     *
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/user/forgotPassword/passwordToken", name:"api_forgotPassword_passwordToken", methods:"POST")]
    public function check(Request $request): JsonResponse
    {
        try {
            if ($this->checkPasswordTokenService->checkPasswordToken($request)) {
                $this->setResponseSuccess();
            } else {
                if (!empty($this->checkPasswordTokenService->validationErrors)) {
                    $this->setResponseValidation();
                } else {
                    $this->setResponseCriticalError();
                }
            }
        } catch (Exception $exception) {
            $this->setResponseException($exception);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $this->createResponse->statusCode(Response::HTTP_OK);
        $this->createResponse->success(
            "Ton mot de passe a été correctement modifié."
        );
    }

    private function setResponseValidation(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($this->checkPasswordTokenService->validationErrors);
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->criticalError("Oups, je n'ai pas réussi à modifier ton mot de passe.");
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
