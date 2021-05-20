<?php

namespace App\Controller\Api;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/question", name="api_question")
 */
class QuestionController extends AbstractController
{
    protected QuestionRepository $repository;
    protected SerializerInterface $serializer;
    protected EntityManagerInterface $manager;
    protected ValidatorInterface $validator;

    public function __construct(QuestionRepository $repository, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
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
    public function single(Question $question): Response
    {
        return $this->makeJsonResponse($question);
    }

    /**
     * @Route("/", name="_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        // Transforme le code JSON reçu par le client en objet
        $question = $this->serializer->deserialize($request->getContent(), Question::class, 'json');
        $this->manager->merge($question->getQuiz());
        // Vérifie que les propriétés de l'objet obtenu correspondent bien aux contraintes définies dans la classe
        $violationList = $this->validator->validate($question);
        // Si l'objet n'est pas conforme aux contraintes
        if (!empty($violationList->violations)) {
            // Renvoie une erreur 400 avec la liste des contraintes non respectées
            return $this->makeJsonResponse($violationList, Response::HTTP_BAD_REQUEST);
        }

        // Envoie l'objet obtenu en base de données
        $this->manager->persist($question);
        $this->manager->flush();
        // Renvoie une réponse de succès avec une copie de l'objet envoyé en base de données
        return $this->makeJsonResponse($question, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="_update", methods={"PUT"}, requirements={"id":"\d+"})
     */
    public function update(Question $question, Request $request)
    {
        // Transforme le code JSON reçu par le client en objet
        $newQuestion = $this->serializer->deserialize($request->getContent(), Question::class, 'json');
        $newQuestion->setId($question->getId());
        // Vérifie que les propriétés de l'objet obtenu correspondent bien aux contraintes définies dans la classe
        $violationList = $this->validator->validate($question);
        // Si l'objet n'est pas conforme aux contraintes
        if (!empty($violationList->violations)) {
            // Renvoie une erreur 400 avec la liste des contraintes non respectées
            return $this->makeJsonResponse($violationList, Response::HTTP_BAD_REQUEST);
        }

        // Envoie l'objet obtenu en base de données
        $this->manager->merge($newQuestion);
        $this->manager->flush();
        // Renvoie une réponse de succès avec une copie de l'objet envoyé en base de données
        return $this->makeJsonResponse($newQuestion);   
    }

    /**
     * @Route("/{id}", name="_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Question $question)
    {
        $this->manager->remove($question);
        $this->manager->flush();
        return $this->makeJsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/answers", name="_list_answers", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function getAnswers(Question $question)
    {
        return $this->makeJsonResponse(
            $question->getAnswers()
        );
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