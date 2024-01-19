<?php

namespace App\Controller\note;

use Exception;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Response;
use App\Services\note\UpdateCheckedNoteService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class UpdateCheckedController extends AbstractController
{
    private CreateResponseService $createResponse;
    private SerializerInterface $serializer;
    private UpdateCheckedNoteService $updateCheckedNoteService;

    public function __construct(
        CreateResponseService $createResponse,
        SerializerInterface $serializer,
        UpdateCheckedNoteService $updateCheckedNoteService
    ) {
        $this->createResponse = $createResponse;
        $this->serializer = $serializer;
        $this->updateCheckedNoteService = $updateCheckedNoteService;
    }

    /**
     *
     * Modification of the status of a task
     *
     * @OA\Response(
     *     response=201,
     *     description="The task has been properly changed",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200"),
     *         @OA\Property(property="success", type="string", example="Message success"),
     *         @OA\Property(property="datas", type="array", @OA\Items(
     *             type="object",
     *             ref="#/components/schemas/CheckTask"
     *         ))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="The task was not found",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="404"),
     *         @OA\Property(property="critical_error", type="string", example="Critical error message")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="An exception has been lifted",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="500"),
     *         @OA\Property(property="exception", type="string", example="Exception message")
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="The user has not access this resource",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="401"),
     *         @OA\Property(property="message", type="string", example="JWT Token not found")
     *     )
     * )
     *
     * @OA\Patch(
     *     path="/api/note/task/checked/{id}",
     *     summary="Modification of the Status Checke of a task",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The task identifier to modify",
     *         @OA\Schema(type="integer")
     *     )
     * )
     *
     * @OA\Tag(name="Task")
     *
     * @param int $id
     * @return JsonResponse
    */
    #[Route("/api/note/task/checked/{id}", name:"api_setTaskChecked", methods:"PATCH")]
    public function updateTaskChecked(int $id): JsonResponse
    {
        try {
            if ($this->updateCheckedNoteService->checked($id)) {
                $this->setResponseSuccess();
            } else {
                $this->setResponseCriticalError();
            }
        } catch (Exception $e) {
            $this->setResponseException($e);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $task = $this->updateCheckedNoteService->task;
        $message = $task->isChecked() ?
        "J'ai marqué ta tâche comme étant terminée. Beau travail d'équipe !" :
        "Je décoche ta tâche, apparemment, tu ne l'as pas terminée.";

        $this->createResponse->statusCode(Response::HTTP_CREATED);
        $this->createResponse->success($message);

        $context = SerializationContext::create()->setGroups(['task:checked']);
        $this->createResponse->datas($this->serializer->serialize($task, 'json', $context));
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_NOT_FOUND);
        $this->createResponse->criticalError(
            "Alala, je suis désolé, je n'arrive pas à trouver la tâche que tu essaies de modifier."
        );
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
