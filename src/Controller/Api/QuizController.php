<?php

namespace App\Controller\Api;

use App\Repository\QuizRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/quiz", name="api_quiz")
 */
class QuizController extends AbstractController
{
    protected QuizRepository $repository;
    protected SerializerInterface $serializer;

    public function __construct(QuizRepository $repository, SerializerInterface $serializer)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="_list")
     */
    public function list(): Response
    {
        $quizzes = $this->repository->findAll();

        return $this->makeJsonResponse($quizzes);
    }

    /**
     * Create JSON response
     *
     * @param mixed $data The JSON data encoded as a string
     * @param integer $status The response status code (default: 200)
     * @param array $headers The list of additional response headers (default: [])
     * @return JsonResponse
     */
    protected function makeJsonResponse($data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($data, 'json'),
            $status,
            $headers,
            true
        );
    }
}
