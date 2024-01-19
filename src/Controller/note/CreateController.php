<?php

namespace App\Controller\note;

use Exception;
use OpenApi\Annotations as OA;
use JMS\Serializer\SerializerInterface;
use App\Services\note\CreateNoteService;
use JMS\Serializer\SerializationContext;
use App\Services\tools\CreateResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CreateController extends AbstractController
{
    private CreateNoteService $createNoteService;
    private CreateResponseService $createResponse;
    private SerializerInterface $serializer;

    public function __construct(
        CreateNoteService $createNoteService,
        CreateResponseService $createResponse,
        SerializerInterface $serializer
    ) {
        $this->createNoteService = $createNoteService;
        $this->createResponse = $createResponse;
        $this->serializer = $serializer;
    }

    /**
     *
     * Creation of a note
     *
     * @OA\Response(
     *     response=201,
     *     description="The note has been created ",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="201"),
     *         @OA\Property(property="datas", type="array", @OA\Items(
     *             type="object",
     *             ref="#/components/schemas/Note"
     *         ))
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="An entitled validation error or missing data",
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
     *     description="The user does not have access to this resource",
     *     @OA\JsonContent(
     *         @OA\Property(property="status_code", type="number", example="401"),
     *         @OA\Property(property="message", type="string", example="JWT Token not found")
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
     * @param Request $request
     * @return JsonResponse
    */
    #[Route("/api/notes", name:"api_setNote", methods:"POST")]
    public function setNote(Request $request): JsonResponse
    {
        try {
            $result = $this->createNoteService->create($request);
            if ($result) {
                $this->setResponseSuccess();
            } else {
                if (!empty($this->createNoteService->validationErrors)) {
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
        $context = SerializationContext::create()->setGroups(['note:read']);
        $datas = $this->serializer->serialize($this->createNoteService->note, 'json', $context);

        $this->createResponse->statusCode(Response::HTTP_CREATED);
        $this->createResponse->success("J'ai créé une nouvelle note, rien que pour toi.");
        if ($this->createNoteService->ignored_task !== 0) {
            $pluriel = $this->createNoteService->ignored_task > 1 ?
                "Aïe, $this->createNoteService->ignored_task notes m'ont attaqué et se sont échappées." :
                "Aïe, une note m'a attaqué et s'est échappée.";

            $this->createResponse->warning($pluriel);
        }
        $this->createResponse->datas($datas);
    }

    private function setResponseValidation(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->validation($this->createNoteService->validationErrors);
    }

    private function setResponseCriticalError(): void
    {
        $this->createResponse->statusCode(Response::HTTP_BAD_REQUEST);
        $this->createResponse->criticalError(
            "Ha..., tu as déjà créé suffisamment de notes. La limite est de " . $this->createNoteService::MAX_NOTE
        );
    }

    private function setResponseException(Exception $exception): void
    {
        $this->createResponse->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->createResponse->exception($exception);
    }
}
