<?php

namespace App\Tests\controllers\note;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateCheckedControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?object $urlGenerator;

    private User $user;

    private ContainerInterface $container;

    private int $task_id;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->urlGenerator = $this->container->get('router.default');

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $repository->findAll()[0];

        $note = $this->user->getNotes()->first();
        $firstTask = $note->getTasks()->first();
        $this->task_id = $firstTask->getId();
    }

    /**
     * Test the update checked of a task with success
     */
    public function testUpdateCheckedTaskKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_setTaskChecked', ['id' => $this->task_id]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $responseDatas = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_CREATED, $responseDatas->status_code);
        $this->assertNotEmpty($responseDatas->success);
        $this->assertNotEmpty($responseDatas->datas);
    }

    /**
     * Test the update checked of a task with an unauthenticized user
     */
    public function testUpdateCheckedTaskErrorAuthKO(): void
    {
        //$this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_setTaskChecked', ['id' => $this->task_id]),
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
     * Test the update checked of a task with a critical error
     */
    public function testUpdateCheckedTaskUknownIdKO(): void
    {
        $this->client->loginUser($this->user, 'login');

        $this->client->request(
            Request::METHOD_PATCH,
            $this->urlGenerator->generate('api_setTaskChecked', ['id' => 999]),
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
