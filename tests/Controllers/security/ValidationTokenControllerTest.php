<?php

namespace App\Tests\controllers\security;

use App\Entity\User;
use App\Tests\utils\TokenHelperTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidationTokenControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private ContainerInterface $container;

    private User $user;

    private TokenHelperTest $tokenHelper;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test the validation of a token auth with success
     */
    public function testValidationTokenAuthOK(): void
    {
        $this->tokenHelper->setTokenUser($this->user);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_security_check_auth'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken(),
                'HTTP_TYPE_TOKEN' => 'token_at',
            ]
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
    }

    /**
     * Test the validation of a token forgot password with success
     */
    public function testValidationTokenForgotPasswordOK(): void
    {
        $this->user->setForgotPassword(true);
        $this->tokenHelper->setTokenUser($this->user);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_security_check_auth'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken(),
                'HTTP_TYPE_TOKEN' => 'token_fp',
            ]
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
    }

    /**
     * Test the validation of a token forgot password without access
     */
    public function testValidationTokenForgotPasswordNotAccessOK(): void
    {
        $this->tokenHelper->setTokenUser($this->user);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_security_check_auth'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken(),
                'HTTP_TYPE_TOKEN' => 'token_fp',
            ]
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
    }

    /**
     * Test validation of the token without token in the request
     */
    public function testValidationTokenErrorKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_security_check_auth'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer A1B2C3D4E5"
            ]
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
    }

    /**
     * Test token validation with an exceeded expiration date
     */
    public function testValidationTokenExceededExpirationDateErrorKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_security_check_auth'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ]
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
    }
}
