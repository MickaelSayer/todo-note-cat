<?php

namespace App\Tests\controllers\user\login;

use Exception;
use App\Services\tools\MailerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\user\signUp\CreateUserService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateAccountControllerTest extends WebTestCase
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
     * Test user creation with the right data
     */
    public function testUserCreationSuccess(): void
    {
        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->method('sendTemplateEmail');
        $this->container->set(MailerService::class, $mailerService);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"test@test.com","password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_CREATED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->success);
    }

    /**
     * Testing a user creation with a password that does not respect the regex
     */
    public function testUserCreationBadPasswordRegexError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"test@test.com","password": "123456789"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->password);
    }

    /**
     * Testing a user creation with a empty Password
     */
    public function testUserCreationEmptyPasswordError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"test@test.com","password": ""}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->password);
    }

    /**
     * Testing a user creation with a email that does not respect the regex
     */
    public function testUserCreationBadEmailRegexError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"testZtest.com","password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Testing a user creation with a empty email
     */
    public function testUserCreationEmptyEmailError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"","password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Testing a user creation with a empty email and empty password
     */
    public function testUserCreationEmptyDatasError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"","password": ""}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
        $this->assertNotEmpty($responseDatas->validation->password);
    }

    /**
     * Testing a user creation with a exception
     */
    public function testUserCreationException(): void
    {
        $createUserService = $this->createMock(CreateUserService::class);
        $createUserService
            ->method('create')
            ->willThrowException(new Exception("Une erreur c'est produite pendant la création d'un utilisateur"));
        $this->container->set(CreateUserService::class, $createUserService);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email":"test@test.com","password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }
}
