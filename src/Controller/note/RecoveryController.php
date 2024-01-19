<?php

namespace App\Controller\note;

use Exception;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Services\note\RecoveryNoteService;
use Doctrine\Common\Collections\Collection;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class RecoveryController extends AbstractController
{
    private CreateResponseService $createResponse;
    private SerializerInterface $serializer;
    private RecoveryNoteService $recoveryNoteService;

    public function __construct(
        CreateResponseService $createResponse,
        SerializerInterface $serializer,
        RecoveryNoteService $recoveryNoteService
    ) {
        $this->createResponse = $createResponse;
        $this->serializer = $serializer;
        $this->recoveryNoteService = $recoveryNoteService;
    }

    /**
     *
     * Recovery of notes
     *
     * @OA\Response(
     *     response=200,
     *     description="All the notes we were recovered, or an empty table if no note",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="200"),
     *         @OA\Property(property="datas", type="array", @OA\Items(
     *             type="object",
     *             ref="#/components/schemas/Note"
     *         ))
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
     * @OA\Tag(name="Note")
     *
     * @return JsonResponse
     */
    #[Route("/api/notes", name:"api_getNotes", methods:"GET")]
    public function getNotes(): JsonResponse
    {
        try {
            $notes = $this->recoveryNoteService->recovery();

            $this->setResponseSuccess($notes);
        } catch (Exception $exception) {
            $this->setResponseException($exception);
        }

        $response = $this->createResponse->getResponse();
        return new JsonResponse($response, $response['status_code']);
    }

    private function setResponseSuccess(Collection $notes): void
    {
        $context = SerializationContext::create()->setGroups(['note:read']);
        $datas = $this->serializer->serialize($notes, 'json', $context);

        $this->createResponse->statusCode(Response::HTTP_OK);
        $this->createResponse->datas($datas);
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
