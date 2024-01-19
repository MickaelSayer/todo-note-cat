<?php

namespace App\Tests\controllers\user\login;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FirewallsLoginControllerTest extends WebTestCase
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
     * Test the connection of a user with success and token data
     */
    public function testUserLoginSuccess(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_check'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"mickael.sayer.dev@gmail.com","password": "123456789"}'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertNotEmpty($responseData['token']);

        $user = $this->client->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertSame('mickael.sayer.dev@gmail.com', $user->getEmail());
        $this->assertTrue($user->isValid());
    }

    /**
     * Test a user connection with a bad password
     */
    public function testUserLoginBadPasswordError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_check'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"mickael.sayer.dev@gmail.com","password": "123456987"}'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Test a user connection with a bad username
     */
    public function testUserLoginBadUsernameError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_check'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"mickaael.sayer.dev@gmail.com","password": "123456789"}'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Test a user connection with a all bad datas
     */
    public function testUserLoginAllBadDatasError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_login_check'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"mickaael.sayer.dev@gmail.com","password": "123456987"}'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
