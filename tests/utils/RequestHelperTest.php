<?php

namespace App\Tests\utils;

use Symfony\Component\HttpFoundation\Request;

class RequestHelperTest
{
    private Request $request;
    private array $content = [];
    private array $post = [];

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Recovering the request
     */
    public function getRequest(): Request
    {
        $this->request = new Request([], $this->post, [], [], [], [], json_encode($this->content));

        return $this->request;
    }

    /**
     * Add the title of the note to the request
     *
     * @param string $key_title The key to the title in the request
     * @param string $title The title of the note
     */
    public function setTitleRequest(string $key_title = 'title', string $title = 'Note 0'): void
    {
        $this->content[$key_title] = $title;
    }

    /**
     * Add the descriptions of the task to the request
     *
     * @param string $key_task The key to the tasks in the request
     * @param int $task_empty The task number that will have an empty value
     * @param string $total_task The number of task to add
     */
    public function setTaskRequest(string $key_task = 'tasks', int $task_empty = 0, int $total_task = 2): void
    {
        for ($i = 0; $i < $total_task; $i++) {
            $is_empty = $task_empty !== 0 && $task_empty == ($i + 1);
            $this->content[$key_task][] = [
                'desc' => $is_empty ? '' : "Description $i"
            ];
        }
    }

    /**
     * Add the email of the user to the request
     *
     * @param string $key_email The key to the email in the request
     * @param string $email The email of the user
     */
    public function setEmailRequest(string $key_email = 'email', string $email = 'test@test.test'): void
    {
        $this->content[$key_email] = $email;
    }

    /**
     * Add the password of the user to the request
     *
     * @param string $key_password The key to the password in the request
     * @param string $password The password of the user
     */
    public function setPasswordRequest(string $key_password = 'password', string $password = '0123456789'): void
    {
        $this->content[$key_password] = $password;
    }

    /**
     * Add the token of the user to the request
     *
     * @param string $key_password The key to the password in the request
     * @param string $password The password of the user
     */
    public function setTokenRequest(string $key_token = 'token', string $token = 'A1B2C3D4E5'): void
    {
        $this->content[$key_token] = $token;
    }

    /**
     * Add the token to the post of the request
     *
     * @param string $key_token The key to the parameter
     * @param string $token
     */
    public function setTokenRequestPost(string $key_token = 'token', string $token = 'A1B2C3D4E5'): void
    {
        $this->post[$key_token] = $token;
    }

    /**
     * Recovery of the content of the Decoded request
     */
    public function getRequestContent(): array
    {
        $content = json_decode($this->request->getContent(), true);
        $post = $this->request->request->get('token', null);

        $content['token'] = $post;

        return $content;
    }
}
