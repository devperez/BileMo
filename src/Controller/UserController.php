<?php

namespace App\Controller;

use App\Entity\User;
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
use App\Service\JwtTokenService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    private $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

    /**
     * Fetch the users of an authenticated customer
     */
    #[Route('/api/users', name: 'CustomerUserList', methods: ['GET'])]
    public function getCustomerUserList(UserRepository $userRepository,
    CustomerRepository $customerRepository,
    Request $request,
    SerializerInterface $serializer,
    TagAwareCacheInterface $cache): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            $authenticatedCustomer = $customerRepository->findOneBy(['email' => $customerMail]);
            if ($customerMail)
            {
                $page = $request->get('page', 1);
                $limit = $request->get('limit', 5);
                $idCache = "CustomerUserList-".$page."-".$limit;
                $usersList = $cache->get($idCache, function (ItemInterface $item) use ($authenticatedCustomer, $userRepository, $page, $limit){
                    echo ("Cette liste n'est pas encore mise en cache\n");
                    $item->tag('usersCache');
                    return $userRepository->findAllWithPaginationByCustomer($authenticatedCustomer, $page, $limit);
                });
                $jsonUsersList = $serializer->serialize($usersList, 'json', ['groups' => 'getUsers']);
            
                return new Response($jsonUsersList, Response::HTTP_OK, ['content-Type' => 'application/json']);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Fetch a user of an authenticated customer
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods:['GET'])]
    public function getUserDetail(Request $request, CustomerRepository $customerRepository, UserRepository $userRepository, SerializerInterface $serializer, $id): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            if ($customerMail)
            {
                // Fetch the User entity manually using the UserRepository
                $user = $userRepository->find($id);
                // Check if the User entity was found
                if (!$user) {
                    return new Response('Utilisateur non trouvé.', Response::HTTP_NOT_FOUND);
                }
                $authenticatedCustomer = $customerRepository->findOneBy(['email' => $customerMail]);
                if ($authenticatedCustomer !== $user->getCustomer()) {
                    return new Response('Accès interdit.', Response::HTTP_FORBIDDEN);
                }
                $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);
                
                return new Response($jsonUser, Response::HTTP_OK, ['content-Type' => 'application/json']);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Delete a user of an authenticated customer
     */
    #[Route('api/users/{id}', name:'deleteUser', methods:['DELETE'])]
    public function deleteUser(Request $request,
    UserRepository $userRepository,
    CustomerRepository $customerRepository,
    $id,
    EntityManagerInterface $emi,
    TagAwareCacheInterface $cache): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            if ($customerMail)
            {
                // Fetch the User entity manually using the UserRepository
                $user = $userRepository->find($id);
                // Check if the User entity was found
                if (!$user) {
                    return new Response('Utilisateur non trouvé.', Response::HTTP_NOT_FOUND);
                }
                $authenticatedCustomer = $customerRepository->findOneBy(['email' => $customerMail]);

                if ($authenticatedCustomer !== $user->getCustomer()) {
                    return new Response('Accès interdit.', Response::HTTP_FORBIDDEN);
                }
                $cache->invalidateTags(['usersCache']);
                $emi->remove($user);
                $emi->flush();
                return $this->json(null, Response::HTTP_NO_CONTENT, []);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Create and attach a customer to a user
     */
    #[Route('api/users', name:"createUser", methods:['POST'])]
    public function createUser(Request $request,
    SerializerInterface $serializer,
    CustomerRepository $customerRepository,
    EntityManagerInterface $emi,
    UrlGeneratorInterface $urlGenerator,
    ValidatorInterface $validator,
    TagAwareCacheInterface $cache): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            if ($customerMail)
            {
                $user = $serializer->deserialize($request->getContent(), User::class, 'json');
                // Data validation
                $errors = $validator->validate($user);
                if ($errors->count() > 0)
                {
                    return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
                }
                $cache->invalidateTags(['usersCache']);
                $customer = $customerRepository->findOneBy(['email' => $customerMail]);
                $user->setCustomer($customer);
                $emi->persist($user);
                $emi->flush();
                $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);
                $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    
                return new Response($jsonUser, Response::HTTP_CREATED, ["Location" => $location, 'content-Type' => 'application/json']);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
