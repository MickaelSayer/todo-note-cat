<?php

namespace App\Services\tools;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidatorService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates PHP values against constraints
     *
     * @param object $entity The entity to validate
     *
     * @return ?array The error table, empty if there are not
     */
    public function validate(object $entity): ?array
    {
        $errors = [];

        $violations = $this->validator->validate($entity);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $propertyPath = $violation->getPropertyPath();
                $message = $violation->getMessage();

                $errors[$propertyPath] = $message;
            }
        }

        return array_reverse($errors);
    }

    /**
     * Check that the request data is present
     *
     * @param Request $request Request data
     * @param ?array $datasExpected The expected data
     *
     * @return array Request data
     * @throws  BadRequestHttpException
     */
    public function validateRequest(Request $request, ?array $datasExpected): array
    {
        $requestDatas = json_decode($request->getContent(), true);

        if (!empty($datasExpected)) {
            foreach ($datasExpected as $data) {
                if (!array_key_exists($data, $requestDatas)) {
                    throw new BadRequestHttpException("Data is missing in the applicant");

                    break;
                }
            }
        }

        return $requestDatas;
    }
}
