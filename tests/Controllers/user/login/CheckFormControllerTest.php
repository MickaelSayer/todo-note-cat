<?php

namespace App\Tests\controllers\user\login;

use Exception;
use App\Services\security\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckFormControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');
    }

    /**
     * Test The verification of the connection form data
     */
    public function testValidationFormLoginSuccess(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"Mickael.sayer.dev@gmail.com","password": "123456789"}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * Test validation of data for connection form without email
     */
    public function testValidationFormLoginWithoutEmailError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"","password": "123456789"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Test validation of data for connection form With unorned email
     */
    public function testValidationFormLoginWithInvalidEmailError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"m-iicka86@hotmail.fr","password": "987654321"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->valid);
    }

    /**
     * Test validation of data for connection form with bad password
     */
    public function testValidationFormLoginWithBadPasswordError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"mickael.sayer.dev@gmail.com","password": "123456987"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Test validation of data for connection form with bad email
     */
    public function testValidationFormLoginWithBadEmailError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"mickael.Ssayer.dev@gmail.com","password": "123456789"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Test validation of data for connection form with all bad datas
     */
    public function testValidationFormLoginWithAllBadDatasError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"mickael.Ssayer.dev@gmail.com","password": "123456987"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Test validation of data for connection form with a exception
     */
    public function testValidationFormLoginWithException(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService
            ->method('isValidUser')
            ->willThrowException(
                new Exception("Une erreur c'est produite lors de la verification que l'utilisateur était valide")
            );
        $this->container->set(UserService::class, $userService);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_validation'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"mickael.Ssayer.dev@gmail.com","password": "123456987"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }
}
