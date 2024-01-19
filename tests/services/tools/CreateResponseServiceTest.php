<?php

namespace App\Tests\services\security;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CreateResponseServiceTest extends WebTestCase
{
    private CreateResponseService $createResponseService;

    public function setUp(): void
    {
        $this->createResponseService = new CreateResponseService();
    }

    /**
     * Test the response status code
     */
    public function testgetResponseStatusCodeOK(): void
    {
        $this->createResponseService->statusCode(Response::HTTP_OK);

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['status_code']);
        $this->assertSame(Response::HTTP_OK, $response['status_code']);
    }

    /**
     * Test the response validation
     */
    public function testgetResponseValidationOK(): void
    {
        $this->createResponseService->validation([
            'title' => 'The title is required',
            'description' => 'The description is required',
        ]);

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['validation']);
        $this->assertSame([
            'title' => 'The title is required',
            'description' => 'The description is required',
        ], $response['validation']);
    }

    /**
     * Test the response success
     */
    public function testgetResponseSuccessOK(): void
    {
        $this->createResponseService->success('Test success');

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['success']);
        $this->assertSame('Test success', $response['success']);
    }

    /**
     * Test the response warning
     */
    public function testgetResponseWarningOK(): void
    {
        $this->createResponseService->warning('Test warning');

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['warning']);
        $this->assertSame('Test warning', $response['warning']);
    }

    /**
     * Test the response datas
     */
    public function testgetResponseDatasOK(): void
    {
        $this->createResponseService->datas(json_encode(['title' => 'test']));

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['datas']);
        $this->assertSame(['title' => 'test'], $response['datas']);
    }

    /**
     * Test the response critical error
     */
    public function testgetResponseCriticalErrorOK(): void
    {
        $this->createResponseService->criticalError('Test critical error', 'Test supplementary critical error');

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['critical_error']);
        $this->assertSame('Test critical error Test supplementary critical error', $response['critical_error']);
    }

    /**
     * Test the response authentification exception
     */
    public function testgetResponseAuthenticationExceptionOK(): void
    {
        $exception = new AuthenticationException('Test authentication exception');

        $this->createResponseService->exception($exception);

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['exception']);
    }

    /**
     * Test the response Bad Request Http Exception
     */
    public function testgetResponseBadRequestHttpExceptionOK(): void
    {
        $exception = new BadRequestHttpException('Test bad request http exception');

        $this->createResponseService->exception($exception);

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['exception']);
    }

    /**
     * Test the response unknown exception
     */
    public function testgetResponseUnknownExceptionOK(): void
    {
        $exception = new NonUniqueResultException('Error non unique result');

        $this->createResponseService->exception($exception);

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['exception']);
    }

    /**
     * Test the response token
     */
    public function testgetResponseTokenOK(): void
    {
        $this->createResponseService->token('A1B2C3D4E5');

        $response = $this->createResponseService->getResponse();

        $this->assertNotEmpty($response['token']);
        $this->assertSame('A1B2C3D4E5', $response['token']);
    }
}
