<?php

namespace App\Controller\security;

use Exception;
use OpenApi\Annotations as OA;
use App\Services\security\TokenService;
use App\Services\tools\ValidatorService;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CheckAccessController extends AbstractController
{
    private TokenService $tokenService;
    private CreateResponseService $createResponse;

    public function __construct(
        TokenService $tokenService,
        CreateResponseService $createResponse,
        ValidatorService $validatorService,
    ) {
        $this->tokenService = $tokenService;
        $this->createResponse = $createResponse;
        $this->validatorService = $validatorService;
    }

    /**
     *
     * Check the token
     *
     * @OA\Response(
     *     response=200,
     *     description="The token was validated",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200")
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="The token was not validated or does not give access to the modification of the password",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="400")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="The note identifier to delete",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="500")
     *     )
     * )
     *
     * @OA\Parameter(
     *    name="type_token",
     *    in="header",
     *    description="Type Token",
     *    required=true,
     *    @OA\Schema(type="string")
     * )
     *
     * @Security(name="BearerCheckToken"),
     * @OA\Tag(name="Security")
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/api/user/security/check", name:"api_security_check_auth", methods:"GET")]
    public function checkValidationTokenAuth(Request $request): JsonResponse
    {
        $type_token = $request->headers->get('TYPE_TOKEN');
        dump($request); exit;
        $status_code = Response::HTTP_BAD_REQUEST;
        try {
            $user = $this->tokenService->getUserAuth();
            if ($user !== null && ($type_token === "token_at" || $type_token === "token_fp")) {
                $status_code = Response::HTTP_OK;
                if ($type_token === "token_fp" && !$user->isForgotPassword()) {
                    $status_code = Response::HTTP_BAD_REQUEST;
                }
            }
        } catch (Exception $exception) {
            $status_code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $this->createResponse->statusCode($status_code);

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }
}
