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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\JwtTokenService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Class UserController
 */
class UserController extends AbstractController
{
    /**
     * @var JwtTokenService
     */
    private $jwtTokenService;

    /**
     * UserController constructor.
     * @param JwtTokenService $jwtTokenService
     */

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

    /**
     * Fetch the users of an authenticated customer
     * 
     * @OA\Response(
     *     response=200,
     *     description="Returns a list of users associated to the authenticated customer",
     *     @OA\JsonContent(
     *         type="object",
     *          @OA\Property(property="items", type="array", @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))),
     *         @OA\Property(property="page", type="integer"),
     *         @OA\Property(property="limit", type="integer"),
     *         @OA\Property(property="totalItems", type="integer"),
     *         @OA\Property(property="totalPages", type="integer"),
     *         @OA\Property(property="nextPage", type="string", description="Link to the next page"),
     *         @OA\Property(property="prevPage", type="string", description="Link to the previous page"),
     *         @OA\Property(property="context", type="string", description="Serialization context"),
     *     )
     * )
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="The page you wish to fetch :",
     *      @OA\Schema(type="int", default=1)
     * )
     * @OA\Parameter(
     *      name="limit",
     *      in="query",
     *      description="The number of elements you wish to fetch :",
     *      @OA\Schema(type="int", default=5)
     * )
     * @OA\Tag(name="Users")
     * 
     * @param UserRepository $userRepository The user repository
     * @param CustomerRepository $customerRepository The customer repository
     * @param Request $request The HTTP request
     * @param SerializerInterface $serializer The serializer service
     * @param TagAwareCacheInterface $cache The cache service
     * @return Response
     */
    #[Route('/api/users', name: 'CustomerUserList', methods: ['GET'], defaults: [
        '_role' => 'customer',
    ])]
    public function getCustomerUserList(
        UserRepository $userRepository,
        CustomerRepository $customerRepository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
        ): Response
        {
            try {
                $customerId = $this->jwtTokenService->getCustomerIdFromRequest($request, $customerRepository);
                $page = $request->get('page', 1);
                $limit = $request->get('limit', 5);
                $idCache = "CustomerUserList-" . $page . "-" . $limit . $customerId;
                if ($page > 0 && $limit > 0 && $limit <= 50) {
                    $usersList = $cache->get($idCache, function (ItemInterface $item) use ($customerId, $userRepository, $page, $limit) {
                        $item->tag('usersCache');
                        return $userRepository->findAllWithPaginationByCustomer($customerId, $page, $limit);
                    });
                    $totalItems = $userRepository->countAllByCustomer($customerId);
                    $totalPages = ceil($totalItems / $limit);
                    $nextPage = $page < $totalPages ? $this->generateUrl('CustomerUserList', [
                        'page' => $page + 1,
                        'limit' => $limit,
                    ], UrlGeneratorInterface::ABSOLUTE_URL) : null;
                    $prevPage = $page > 1 ? $this->generateUrl('CustomerUserList', [
                        'page' => $page - 1,
                        'limit' => $limit,
                    ], UrlGeneratorInterface::ABSOLUTE_URL) : null;
                    $context = SerializationContext::create()->setGroups(['getUsers']);
                    $response = new Response(
                        $serializer->serialize([
                            'items' => $usersList,
                            'page' => $page,
                            'limit' => $limit,
                            'totalItems' => $totalItems,
                            'totalPages' => $totalPages,
                            'nextPage' => $nextPage,
                            'prevPage' => $prevPage,
                        ], 'json', $context),
                        Response::HTTP_OK,
                        [
                            'Content-Type' => 'application/json',
                        ]
                    );
                    return $response;
                } else {
                    return new Response('The limit parameter must be a positive integer inferior to 51.');
                }
            } catch (\Exception $e) {
                return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
            }
        }


    /**
     * Fetch a user of an authenticated customer
     * 
     * @OA\Response(
     *     response=200,
     *     description="Returns a user associated to the authenticated customer",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="Users")
     *
     * @param Request $request The HTTP request
     * @param CustomerRepository $customerRepository The customer repository
     * @param UserRepository $userRepository The user repository
     * @param SerializerInterface $serializer The serializer service
     * @param $id The user id
     * @return Response
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'], defaults: [
        '_role' => 'customer',
    ])]
    public function getUserDetail(Request $request, CustomerRepository $customerRepository, UserRepository $userRepository, SerializerInterface $serializer, $id): Response
    {
        try {
            $customerId = $this->jwtTokenService->getCustomerIdFromRequest($request, $customerRepository);
            // Fetch the User entity manually using the UserRepository
            $user = $userRepository->find($id);
            // Check if the User entity was found
            if (!$user) {
                return new Response('Utilisateur non trouvé.', Response::HTTP_NOT_FOUND);
            }
            $authenticatedCustomer = $customerRepository->findOneBy([
                'id' => $customerId,
            ]);
            if ($authenticatedCustomer !== $user->getCustomer()) {
                return new Response('Accès interdit.', Response::HTTP_FORBIDDEN);
            }
            $context = SerializationContext::create()->setGroups(['getUsers']);
            $response = new Response(
                $serializer->serialize($user, 'json', $context),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/json',
                ]
            );
            return $response;
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Delete a user of an authenticated customer
     * 
     * @OA\Response(
     *     response=200,
     *     description="Deletes a user associated to the authenticated customer",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="Users")
     * 
     * @param Request $request The HTTP request
     * @param UserRepository $userRepository The user repository
     * @param CustomerRepository $customerRepository The customer repository
     * @param $id The user id
     * @param EntityManagerInterface $emi The entity manager
     * @param TagAwareCacheInterface $cache The cache service
     * @return Response
     */
    #[Route('api/users/{id}', name: 'deleteUser', methods: ['DELETE'], defaults: [
        '_role' => 'customer',
    ])]
    public function deleteUser(
        Request $request,
        UserRepository $userRepository,
        CustomerRepository $customerRepository,
        $id,
        EntityManagerInterface $emi,
        TagAwareCacheInterface $cache
    ): Response {
        try {
            $customerId = $this->jwtTokenService->getCustomerIdFromRequest($request, $customerRepository);
            // Fetch the User entity manually using the UserRepository
            $user = $userRepository->find($id);
            // Check if the User entity was found
            if (!$user) {
                return new Response('Utilisateur non trouvé.', Response::HTTP_NOT_FOUND);
            }
            $authenticatedCustomer = $customerRepository->findOneBy([
                'id' => $customerId,
            ]);

            if ($authenticatedCustomer !== $user->getCustomer()) {
                return new Response('Accès interdit.', Response::HTTP_FORBIDDEN);
            }
            $cache->invalidateTags(['usersCache']);
            $emi->remove($user);
            $emi->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT, []);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Create and attach a user to a customer
     * 
     * @OA\Response(
     *     response=200,
     *     description="Creates and associates a user to the authenticated customer",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\RequestBody(
     *      request="createUser",
     *      description="User's data",
     *      required=true,
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="firstName", type="property_type", example="John"),
     *          @OA\Property(property="lastName", type="property_type", example="Doe"),
     *          @OA\Property(property="userName", type="property_type", example="john_doe"),
     *          @OA\Property(property="email", type="property_type", example="john.doe@example.com"),
     *      )
     * )
     * @OA\Tag(name="Users")
     * 
     * @param Request $request The HTTP request
     * @param SerializerInterface $serializer The serializer service
     * @param CustomerRepository $customerRepository The customer repository
     * @param EntityManagerInterface $emi The entity manager
     * @param UrlGeneratorInterface $urlGenerator The URL generator
     * @param ValidatorInterface $validator The validator
     * @param TagAwareCacheInterface $cache The cache service
     * @return Response
     */
    #[Route('api/users', name: "createUser", methods: ['POST'], defaults: [
        '_role' => 'customer',
    ])]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        CustomerRepository $customerRepository,
        EntityManagerInterface $emi,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ): Response {
        try {
            $customerId = $this->jwtTokenService->getCustomerIdFromRequest($request, $customerRepository);
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            // Data validation
            $errors = $validator->validate($user);
            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
            $cache->invalidateTags(['usersCache']);
            $customer = $customerRepository->findOneBy([
                'id' => $customerId,
            ]);
            $user->setCustomer($customer);
            $emi->persist($user);
            $emi->flush();

            $context = SerializationContext::create()->setGroups(['getUsers']);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            $location = $urlGenerator->generate('detailUser', [
                'id' => $user->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return new Response($jsonUser, Response::HTTP_CREATED, [
                "Location" => $location,
                'content-Type' => 'application/json',
            ]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
