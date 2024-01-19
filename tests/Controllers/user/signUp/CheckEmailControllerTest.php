<?php

namespace App\Tests\controllers\user\login;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\utils\TokenHelperTest;
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

    private TokenHelperTest $tokenHelper;

    private UserRepository $userRepository;

    private User $user;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $this->userRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->userRepository->findAll()[1];

        $this->tokenHelper = static::getContainer()->get('tests.token_helper');
    }

    /**
     * Test validation of the token for validation of email address
     */
    public function testCheckValidationEmailSuccess(): void
    {
        $this->tokenHelper->setTokenUser($this->user);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp_email'),
            ['token' => $this->user->getToken()],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseRedirects('/login?validEmail=1', Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * Test the validation of an email with the empty token
     */
    public function testCheckValidationEmailEmptytokenError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp_email'),
            ['token' => ''],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseRedirects('/signUp?validEmail=0', Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * Test the validation of an email with the empty user
     */
    public function testCheckValidationEmailBadTokenError(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp_email'),
            ['token' => 'A54fdg54df54gdf5g4d'],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseRedirects('/signUp?validEmail=0', Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * Test the validation of the email with an expired token
     */
    public function testCheckValidationEmailTokenExpirateError(): void
    {
        $this->tokenHelper->setTokenUser($this->user, true);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_signUp_email'),
            ['token' => $this->user->getToken()],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseRedirects('/signUp?validEmail=0', Response::HTTP_TEMPORARY_REDIRECT);
    }
}
