<?php

namespace App\Controller\note;

use Exception;
use JMS\Serializer\SerializerInterface;
use App\Services\note\UpdateNoteService;
use JMS\Serializer\SerializationContext;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class UpdateController extends AbstractController
{
    private UpdateNoteService $updateNoteService;
    private CreateResponseService $createResponse;
    private SerializerInterface $serializer;

    public function __construct(
        UpdateNoteService $updateNoteService,
        CreateResponseService $createResponse,
        SerializerInterface $serializer
    ) {
        $this->updateNoteService = $updateNoteService;
        $this->createResponse = $createResponse;
        $this->serializer = $serializer;
    }

    /**
     *
     * Modification of a note
     *
     * @OA\Response(
     *     response=201,
     *     description="The note has been properly changed",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200"),
     *         @OA\Property(property="datas", type="array", @OA\Items(
     *             type="object",
     *             ref="#/components/schemas/Note"
     *         ))
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="The note has not been found, or a validation error of the entity",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="400"),
     *         @OA\Property(property="validation", type="object", ref="#/components/schemas/Validation"),
     *         @OA\Property(property="critical_error", type="string", example="Message critical error")
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
     *     path="/api/notes/{id}",
     *     summary="The note identifier to update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The note identifier to update",
     *         @OA\Schema(type="integer")
     *     )
     * )
     *
     * @OA\RequestBody(
     *     required=true,
     *     description="Request body",
     *     @OA\JsonContent(
     *         @OA\Property(property="title", type="string", example="Example Title"),
     *         @OA\Property(property="tasks", type="array", @OA\Items(
     *             type="object",
     *             @OA\Property(property="desc", type="string", example="Example description")
     *         ))
     *     ),
     * )
     *
     * @OA\Tag(name="Note")
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/notes/{id}", name:"api_updateNote", methods:"PATCH")]
    public function updateNotes(int $id, Request $request): JsonResponse
    {
        try {
            if ($this->updateNoteService->update($id, $request)) {
                $this->setResponseSuccess();
            } else {
                if (!empty($this->updateNoteService->validationErrors)) {
                    $this->setResponseValidation();
                } else {
                    $this->setResponseCriticalError();
                }
            }
        } catch (Exception $e) {
            $this->setResponseException($e);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $note = $this->updateNoteService->note;
        $ignored_task = $this->updateNoteService->ignored_task;

        $context = SerializationContext::create()->setGroups(['note:update']);
        $datas = $this->serializer->serialize($note, 'json', $context);

        $this->createResponse->statusCode(Response::HTTP_CREATED);
        $this->createResponse->success(
            "J'ai modifié ta note avec succès. Elle est magnifique, regarde !"
        );
        if ($ignored_task !== 0) {
            $pluriel = $ignored_task > 1 ?
                "Tu as crée trop de tâche, grâce à toi, $ignored_task tâches sont parties vers un monde meilleur." :
                "Tu as créé trop de tâches, grâce à toi, une tâche est partie vers un monde meilleur.";
            $this->createResponse->warning($pluriel);
        }
        $this->createResponse->datas($datas);
    }

    private function setResponseValidation(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($this->updateNoteService->validationErrors);
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_NOT_FOUND);
        $this->createResponse->criticalError(
            'Malheureusement... Je suis désolé, ta note a disparu. Je fais de mon mieux pour la retrouver.'
        );
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
