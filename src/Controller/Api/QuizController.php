<?php

namespace App\Controller\Api;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/quiz", name="api_quiz")
 */
class QuizController extends AbstractController
{
    protected QuizRepository $repository;
    protected SerializerInterface $serializer;
    protected EntityManagerInterface $manager;
    protected ValidatorInterface $validator;

    public function __construct(QuizRepository $repository, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/", name="_list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->makeJsonResponse(
            $this->repository->findAll()
        );
    }

    /**
     * @Route("/{id}", name="_single", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function single(Quiz $quiz): Response
    {
        return $this->makeJsonResponse($quiz);
    }

    /**
     * @Route("/", name="_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        // Transforme le code JSON reçu par le client en objet
        $quiz = $this->serializer->deserialize($request->getContent(), Quiz::class, 'json');
        // Vérifie que les propriétés de l'objet obtenu correspondent bien aux contraintes définies dans la classe
        $violationList = $this->validator->validate($quiz);
        // Si l'objet n'est pas conforme aux contraintes
        if (!empty($violationList->violations)) {
            // Renvoie une erreur 400 avec la liste des contraintes non respectées
            return $this->makeJsonResponse($violationList, Response::HTTP_BAD_REQUEST);
        }

        // Envoie l'objet obtenu en base de données
        $this->manager->persist($quiz);
        $this->manager->flush();
        // Renvoie une réponse de succès avec une copie de l'objet envoyé en base de données
        return $this->makeJsonResponse($quiz, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="_update", methods={"PUT"}, requirements={"id":"\d+"})
     */
    public function update(Quiz $quiz, Request $request)
    {
        // Transforme le code JSON reçu par le client en objet
        $newQuiz = $this->serializer->deserialize($request->getContent(), Quiz::class, 'json');
        $newQuiz->setId($quiz->getId());
        // Vérifie que les propriétés de l'objet obtenu correspondent bien aux contraintes définies dans la classe
        $violationList = $this->validator->validate($quiz);
        // Si l'objet n'est pas conforme aux contraintes
        if (!empty($violationList->violations)) {
            // Renvoie une erreur 400 avec la liste des contraintes non respectées
            return $this->makeJsonResponse($violationList, Response::HTTP_BAD_REQUEST);
        }

        // Envoie l'objet obtenu en base de données
        $this->manager->merge($newQuiz);
        $this->manager->flush();
        // Renvoie une réponse de succès avec une copie de l'objet envoyé en base de données
        return $this->makeJsonResponse($newQuiz);   
    }

    /**
     * @Route("/{id}", name="_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Quiz $quiz)
    {
        $this->manager->remove($quiz);
        $this->manager->flush();
        return $this->makeJsonResponse(null, Response::HTTP_NO_CONTENT);
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
        if (is_null($data)) {
            $json = '';
        } else {
            $json = $this->serializer->serialize($data, 'json');
        }

        return new JsonResponse(
            $json,
            $status,
            $headers,
            true
        );
    }
}
