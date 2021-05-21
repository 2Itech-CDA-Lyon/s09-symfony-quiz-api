<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Security\TokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    protected SerializerInterface $serializer;
    protected EntityManagerInterface $manager;
    protected ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/current-user", name="_current_user")
     */
    public function currentUser(): Response
    {
        $user = $this->getUser();
        if (is_null($user)) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return $this->makeJsonResponse($user);
    }
    

    /**
     * @Route("/api/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UserRepository $repository, GuardAuthenticatorHandler $guardHandler, TokenAuthenticator $authenticator)
    {
        // Décode le contenu de la requête
        $payload = \json_decode($request->getContent(), true);

        // Si la requête ne contient pas les champs requis
        if (!isset($payload['email']) || !isset($payload['password'])) {
            // Informe le client que la requête est mal formée
            return new JsonResponse(['message' => 'Invalid request body.'], Response::HTTP_BAD_REQUEST);
        }

        // Récupère l'utilisateur correspondant à l'email fourni
        $user = $repository->findOneBy([ 'email' => $payload['email'] ]);

        // Si aucune utilisateur ne possède l'email fourni
        if (is_null($user)) {
            // Informe le client que l'email n'existe pas
            return new JsonResponse(['message' => 'User does not exist.'], Response::HTTP_UNAUTHORIZED);
        }

        // Si le mot de passe fourni correspond bien au mot de passe de l'utilisateur
        if (\password_verify($payload['password'], $user->getPassword())) {
            // Authentifie l'utilisateur dans l'API (en créant un cookie)
            $guardHandler->authenticateUserAndHandleSuccess(
                $user,          // the User object you just created
                $request,
                $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                'main'          // the name of your firewall in security.yaml
            );
            // Renvoie au client le jeton d'API correspondant à l'utilisateur authentifié
            return new JsonResponse(['apiToken' => $user->getApiToken()]);
        }

        // Sinon, informe le client que le mot de passe est incorrect
        return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @Route("/api/logout", name="_logout")
     */
    public function logout(): Response
    {

    }

    /**
     * @Route("/api/after_logout", name="_after_logout")
     */
    public function afterLogout(): Response
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
