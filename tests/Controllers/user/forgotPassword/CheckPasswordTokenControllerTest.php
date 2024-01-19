<?php

namespace App\Tests\controllers\security;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\TokenHelperTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Services\user\forgotPassword\CheckPasswordTokenService;

class CheckPasswordTokenControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private ContainerInterface $container;

    private TokenHelperTest $tokenHelper;

    private UserRepository $userRepository;

    private User $user;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $this->userRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->userRepository->findAll()[0];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test The password modification with success
     */
    public function testUpdatePasswordOK(): void
    {
        $this->user->setForgotPassword(true);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->success);
    }

    /**
     * Test The password modification with empty token
     */
    public function testUpdatePasswordEmptyUserKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer A1B2C3D4E5"
            ],
            '{"password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
    }

    /**
     * Test The password modification with expirate token
     */
    public function testUpdatePasswordExpirateTokenErrorKO(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
    }

    /**
     * Test The password modification with empty password
     */
    public function testUpdatePasswordEmptyPasswordErrorKO(): void
    {
        $this->user->setForgotPassword(true);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": ""}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->password);
    }

    /**
     * Test The password modification with a password that does not respect the regex
     */
    public function testUpdatePasswordNotRespectRegexErrorKO(): void
    {
        $this->user->setForgotPassword(true);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": "123456789"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->password);
    }

    /**
     * Test The password modification with an invalid user
     */
    public function testUpdatePasswordEmailInvalidError(): void
    {
        $this->user
            ->setForgotPassword(true)
            ->setValid(false);
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->critical_error);
    }

    /**
     * Test The password modification with an exception
     */
    public function testUpdatePasswordExceptionKO(): void
    {
        $checkPasswordTokenService = $this->createMock(CheckPasswordTokenService::class);
        $checkPasswordTokenService
            ->method('checkPasswordToken')
            ->willThrowException(new Exception("An error is produced during the modification of the password"));
        $this->container->set(CheckPasswordTokenService::class, $checkPasswordTokenService);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_forgotPassword_passwordToken'),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer " . $this->user->getToken()
            ],
            '{"password": "A1@aaaaa"}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }
}
