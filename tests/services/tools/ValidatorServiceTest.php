<?php

namespace App\Tests\services\security;

use App\Entity\Note;
use App\Tests\utils\RequestHelperTest;
use App\Services\tools\ValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidatorServiceTest extends WebTestCase
{
    private ValidatorInterface|MockObject $validatorMock;

    private RequestHelperTest $requesthelperTest;
    private array $requestExpected = ['title', 'tasks'];

    private ValidatorService $validatorService;

    public function setUp(): void
    {
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->requesthelperTest = new RequestHelperTest();

        $this->validatorService = new ValidatorService($this->validatorMock);
    }

    /**
     * test the validation of an entity with success
     */
    public function testValidateOK(): void
    {
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with(new Note())
            ->willReturn(new ConstraintViolationList([]));

        $errors = $this->validatorService->validate(new Note());

        $this->assertEmpty($errors);
    }

    /**
     * test the validation of an entity with errors
     */
    public function testValidateKO(): void
    {
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with(new Note())
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('Error title', null, [], null, 'title', null)
            ]));

        $errors = $this->validatorService->validate(new Note());

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Error title', $errors['title']);
    }

    /**
     * Test validation of the request without parameter
     */
    public function testValidateRequestOK(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $requestContent = $this->validatorService->validateRequest($request, $this->requestExpected);

        $this->assertNotEmpty($requestContent);
        $this->assertArrayHasKey('title', $requestContent);
        $this->assertArrayHasKey('tasks', $requestContent);
        $this->assertArrayNotHasKey('token', $requestContent);
    }

    /**
     * Test validation of the request untitled and without additional parameter
     */
    public function testValidateRequesNotIssetTitletKO(): void
    {
        $this->requesthelperTest->setTitleRequest('test');
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        try {
            $this->validatorService->validateRequest($request, $this->requestExpected);
        } catch (BadRequestHttpException $exception) {
            $this->assertSame("Data is missing in the applicant", $exception->getMessage());
        }
    }

    /**
     * Test validation of the request with a post supplementary parameter
     */
    public function testValidateRequestWithOneParameterOK(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest();
        $request = $this->requesthelperTest->getRequest();

        $requestContent = $this->validatorService->validateRequest($request, $this->requestExpected);

        $this->assertNotEmpty($requestContent);
        $this->assertArrayHasKey('title', $requestContent);
        $this->assertArrayHasKey('tasks', $requestContent);
    }

    /**
     * Test validation of the request with a post supplementary parameter empty
     */
    public function testValidateRequestWithOneNotIssetParameterKO(): void
    {
        $this->requesthelperTest->setTitleRequest();
        $this->requesthelperTest->setTaskRequest('test');
        $request = $this->requesthelperTest->getRequest();

        try {
            $this->validatorService->validateRequest($request, $this->requestExpected);
        } catch (BadRequestHttpException $exception) {
            $this->assertSame("Data is missing in the applicant", $exception->getMessage());
        }
    }
}
