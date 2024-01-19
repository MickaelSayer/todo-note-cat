<?php

namespace App\Tests\controllers\note;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private User $user;

    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];
    }

    /**
     * Test the creation of a note with success
     */
    public function testCreateNoteSuccessOK(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","tasks":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_CREATED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->success);
        $this->assertNotEmpty($responseDatas->datas);
    }

    /**
     * Test the creation of a note with an unauthenticized user
     */
    public function testCreateNoteErrorAuthKO(): void
    {
        //$this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","tasks":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseDatas->code);
        $this->assertEquals('JWT Token not found', $responseDatas->message);
    }

    /**
     * Test creation with an empty title validation error
     */
    public function testCreateNoteErrorValidationTitleNoteKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"","tasks":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->title);
    }

    /**
     * Test creation with an empty description validation error
     */
    public function testCreateNoteErrorValidationDescriptionTaskKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","tasks":[{"desc":""},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->validation->description);
    }

    /**
     * Test the creation of a note with an empty request
     */
    public function testCreateNoteEmptyRequestKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }

    /**
     * Test The creation of a tasks key note
     */
    public function testCreateNoteNotIssetTasksKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","taskss":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }

    /**
     * Test The creation of a Title key note
     */
    public function testCreateNoteNotIssetTitleKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('api_setNote'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"test":"teste","tasks":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }
}
