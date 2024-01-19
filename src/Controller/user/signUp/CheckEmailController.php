<?php

namespace App\Controller\user\signUp;

use Exception;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\user\signUp\EmailValidatorService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CheckEmailController extends AbstractController
{
    private EmailValidatorService $emailValidator;

    public function __construct(EmailValidatorService $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    /**
     *
     * Check the token that the user received by e-mail when registering
     *
     * @OA\Response(
     *     response=200,
     *     description="The token has been verified and validated : redirect /signUp?validEmail=1"
     * )
     * @OA\Response(
     *     response=500,
     *     description="The token has been verified and invalided : redirect /signUp?validEmail=0"
     * )
     *
     * @OA\Post(
     *     path="/api/user/signUp/validation",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Check the token sent by email for the creation of an account",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     description="The token received by email for the creation of an account"
     *                 )
     *             )
     *         )
     *     ),
     *     security={}
     * )
     *
     * @OA\Tag(name="User - SignUp")
     *
     * @param Request $request
     * @return RedirectResponse
    */
    #[Route("/api/user/signUp/validation", name: "api_signUp_email", methods:"POST")]
    public function validation(Request $request): RedirectResponse
    {
        $redirect_route = '/signUp?validEmail=0';

        try {
            if ($this->emailValidator->validateEmail($request)) {
                $redirect_route = '/login?validEmail=1';
            }
        } catch (Exception $e) {
            $redirect_route = '/signUp?validEmail=0';
        }

        return $this->redirect($redirect_route, Response::HTTP_TEMPORARY_REDIRECT);
    }
}
