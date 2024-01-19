<?php

namespace App\Tests\controllers\note;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private object $urlGenerator;

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
     * Test the deletion of a note with success
     */
    public function testDeleteNoteSuccessOK(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_DELETE,
            $this->urlGenerator->generate('api_deleteNote', ['id' => $this->note_id]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * Test the deletion of a note with an unauthenticized user
     */
    public function testDeleteNoteErrorAuthKO(): void
    {
        //$this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_DELETE,
            $this->urlGenerator->generate('api_deleteNote', ['id' => $this->note_id]),
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
     * Test the deletion of a note, with an unknown identifier
     */
    public function testDeleteNoteUnknownIdKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_DELETE,
            $this->urlGenerator->generate('api_deleteNote', ['id' => 0]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_NOT_FOUND, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->critical_error);
    }
}
