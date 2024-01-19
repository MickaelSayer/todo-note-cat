<?php

namespace App\Controller\note;

use Exception;
use App\Services\note\DeleteNoteService;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class DeleteController extends AbstractController
{
    private DeleteNoteService $deleteNoteService;
    private CreateResponseService $createResponse;

    public function __construct(
        DeleteNoteService $deleteNoteService,
        CreateResponseService $createResponse
    ) {
        $this->deleteNoteService = $deleteNoteService;
        $this->createResponse = $createResponse;
    }

    /**
     *
     * Deletion of a note
     *
     * @OA\Response(
     *     response=204,
     *     description="The note has been properly deleted"
     * )
     * @OA\Response(
     *     response=404,
     *     description="The note has not been deleted",
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
     * @OA\Delete(
     *     path="/api/notes/{id}",
     *     summary="The note identifier to delete",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The note identifier to delete",
     *         @OA\Schema(type="integer")
     *     )
     * )
     *
     * @OA\Tag(name="Note")
     *
     * @param int $id
     * @return JsonResponse
     */
    #[Route("/api/notes/{id}", name:"api_deleteNote", methods:"DELETE")]
    public function deleteNote(int $id): JsonResponse
    {
        try {
            if ($this->deleteNoteService->delete($id)) {
                $this->setResponseSuccess();
            } else {
                $this->setResponseCriticalError();
            }
        } catch (Exception $exception) {
            $this->setResponseException($exception);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(): void
    {
        $this->createResponse->statusCode(Response::HTTP_NO_CONTENT);
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_NOT_FOUND);
        $this->createResponse->criticalError(
            "J'ai rencontré une petite erreur. Je cherche, " .
            "mais je ne trouve pas la note que tu essaies de supprimer."
        );
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
