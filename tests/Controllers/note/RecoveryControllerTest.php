<?php

namespace App\Tests\controllers\note;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecoveryControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private User $user;

    private ContainerInterface $container;

    private UserRepository $repository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $this->repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->repository->findAll()[0];
    }

    /**
     * Test the recovery complete notes with success
     */
    public function testRecoveryOK(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_getNotes'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->datas);
    }

    /**
     * Test the recovery of a note with an unauthenticized user
     */
    public function testRecoveryNoteErrorAuthKO(): void
    {
        //$this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_getNotes'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
        $this->assertEquals('JWT Token not found', $responseDatas->message);
    }

    /**
     * Test the recovery without notes with success
     */
    public function testRecoveryWithoutNoteOK(): void
    {
        $user = $this->repository->findAll()[2];
        $this->client->loginUser($user, 'login');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('api_getNotes'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_OK, $responseDatas->status_code);
        $this->assertEmpty($responseDatas->datas);
    }
}
