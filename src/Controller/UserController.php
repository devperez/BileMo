<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    private $jwtManager;
    private $tokenStorageInterface;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    /**
     * Fetch the users of an authenticated customer
     */
    #[Route('/api/users', name: 'CustomerUserList', methods: ['GET'])]
    public function getCustomerUserList(CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                $customerMail = $decodedToken['username'];
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Token decoding error'], Response::HTTP_UNAUTHORIZED);
            }
        }
        $customer = $customerRepository->findOneBy(['email' => $customerMail]);

        $users = $customer->getUsers();
        $jsonUsersList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new Response($jsonUsersList, Response::HTTP_OK, ['content-Type' => 'application/json']);
    }

    /**
     * Fetch a user of an authenticated customer
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods:['GET'])]
    public function getUserDetail(Request $request, CustomerRepository $customerRepository, UserRepository $userRepository, SerializerInterface $serializer, $id): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                $customerMail = $decodedToken['username'];
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Erreur relative au token'], Response::HTTP_UNAUTHORIZED);
            }
        }
        // Fetch the User entity manually using the UserRepository
        $user = $userRepository->find($id);
        // Check if the User entity was found
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        
        $authenticatedCustomer = $customerRepository->findOneBy(['email' => $customerMail]);
        
        if ($authenticatedCustomer !== $user->getCustomer()) {
            return new JsonResponse(['message' => 'Accès interdit.'], Response::HTTP_FORBIDDEN);
        }
        
        $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);

        return new Response($jsonUser, Response::HTTP_OK, ['content-Type' => 'application/json']);
    }

    /**
     * Delete a user of an authenticated customer
     */
    #[Route('api/users/{id}', name:'deleteUser', methods:['DELETE'])]
    public function deleteUser(Request $request, UserRepository $userRepository, CustomerRepository $customerRepository, $id, EntityManagerInterface $emi): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                $customerMail = $decodedToken['username'];
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Erreur relative au token'], Response::HTTP_UNAUTHORIZED);
            }
        }

        // Fetch the User entity manually using the UserRepository
        $user = $userRepository->find($id);
        // Check if the User entity was found
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        
        $authenticatedCustomer = $customerRepository->findOneBy(['email' => $customerMail]);
        
        if ($authenticatedCustomer !== $user->getCustomer()) {
            return new JsonResponse(['message' => 'Accès interdit.'], Response::HTTP_FORBIDDEN);
        }

        $emi->remove($user);
        $emi->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT, []);
    }


    #[Route('api/users', name:"createUser", methods:['POST'])]
    public function createUser(Request $request,
    SerializerInterface $serializer,
    CustomerRepository $customerRepository,
    EntityManagerInterface $emi,
    UrlGeneratorInterface $urlGenerator,
    ValidatorInterface $validator): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                $customerMail = $decodedToken['username'];
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Erreur relative au token'], Response::HTTP_UNAUTHORIZED);
            }
        }

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Data validation
        $errors = $validator->validate($user);
        if ($errors->count() > 0)
        {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        

        $customer = $customerRepository->findOneBy(['email' => $customerMail]);
        $user->setCustomer($customer);

        $emi->persist($user);
        $emi->flush();

        $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new Response($jsonUser, Response::HTTP_CREATED, ["Location" => $location, 'content-Type' => 'application/json']);
    }
}
