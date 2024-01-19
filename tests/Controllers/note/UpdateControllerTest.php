<?php

namespace App\Tests\controllers\note;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private User $user;

    private ContainerInterface $container;

    private int $note_id;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];

        $note = $this->user->getNotes()->first();
        $this->note_id = $note->getId();
    }

    /**
     * Test the update note with success
     */
    public function testUpdateNoteOK(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
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
     * Test the update of a note with an unauthenticized user
     */
    public function testUpdateNoteErrorAuthKO(): void
    {
        //$this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
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
     * Test the update of a note with an error for the title of the note
     */
    public function testUpdateNoteErrorValidationTitleNoteKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
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
     * Test the update of a note with an error for the description of the task
     */
    public function testUpdateNoteErrorValidationDescriptionTaskKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
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
     * Test the update note with the tall title key in the request
     */
    public function testUpdateNoteNotIssetTitleRequestKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
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

    /**
     * Test the update note with the tall description key in the request
     */
    public function testUpdateNoteNotIssetDescriptionRequestKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => $this->note_id]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","test":[{"desc":"teste"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());
 
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->exception);
    }

    /**
     * Test the update of a note with a critical error
     */
    public function testUpdateNoteUknownIdKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_updateNote', ['id' => 999]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"teste","tasks":[{"desc":"test"},{"desc":"teste"}]}'
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_NOT_FOUND, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->critical_error);
    }
}
