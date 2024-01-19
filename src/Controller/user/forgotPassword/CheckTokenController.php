<?php

namespace App\Controller\user\forgotPassword;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Services\user\forgotPassword\CheckTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class CheckTokenController extends AbstractController
{
    private CheckTokenService $checkTokenService;

    public function __construct(CheckTokenService $checkTokenService)
    {
        $this->checkTokenService = $checkTokenService;
    }

    /**
     *
     * Check the token sent by email for the modification of the password (Etape 2)
     *
     * @OA\Response(
     *     response=200,
     *     description="The token has been verified and validated : redirect /forgotPassword",
     * )
     * @OA\Response(
     *     response=500,
     *     description="The token has been verified and invalided : redirect /login?fortgotPassword=0",
     * )
     *
     * @OA\Post(
     *     path="/api/user/forgotPassword/token",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Register the token to check",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     description="The token received by email for the modification of the password"
     *                 )
     *             )
     *         )
     *     ),
     *     security={}
     * )
     *
     * @OA\Tag(name="User - Forgot Password")
     *
     * @param Request $request
     * @return RedirectResponse
    */
    #[Route("/api/user/forgotPassword/token", name:"api_forgotPassword_token", methods:"POST")]
    public function check(Request $request): RedirectResponse
    {
        $redirect = '/login?fortgotPassword=0';

        try {
            if ($this->checkTokenService->validateToken($request)) {
                $redirect = '/forgotPassword';
            }
        } catch (Exception $e) {
            $redirect = '/login?fortgotPassword=0';
        }

        return $this->redirect($redirect, RESPONSE::HTTP_TEMPORARY_REDIRECT);
    }
}
