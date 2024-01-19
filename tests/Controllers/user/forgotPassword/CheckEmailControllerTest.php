<?php

namespace App\Tests\controllers\security;

use App\Services\tools\MailerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckEmailControllerTest extends WebTestCase
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
     * Test the validation of a email with success
     */
    public function testValidationEmailOK(): void
    {
        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->method('sendTemplateEmail');
        $this->container->set(MailerService::class, $mailerService);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_email'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": "Mickael.sayer.dev@gmail.com"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->success);
        $this->assertNotEmpty($responseDatas->token);
    }

    /**
     * Test the validation of a empty email
     */
    public function testValidationEmptyEmailErrorKO(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_email'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": ""}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->email);
    }

    /**
     * Test validation of an onnexisting email
     */
    public function testValidationOnnexistingEmailErrorKO(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_email'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": "test.test@test.com"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->critical_error);
    }

    /**
     * Test validation of an valid email
     */
    public function testValidationValidEmailErrorKO(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_email'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": "m-iicka86@hotmail.fr"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->critical_error);
    }

    /**
     * Test validation of email with exception
     */
    public function testValidationEmailExceptionKO(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_email'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"emaoil": "mickael.sayer.dev@gmail.com"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }
}
