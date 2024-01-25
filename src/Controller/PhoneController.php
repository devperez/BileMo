<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\JwtTokenService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class PhoneController extends AbstractController
{
    private $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }
    /**
     * Fetch the whole phone catalogue
     * 
     * @OA\Response(
     *     response=200,
     *     description="Returns the whole phone list",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="The page you wish to fetch :",
     *      @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *      name="limit",
     *      in="query",
     *      description="The number of elements you wish to fetch :",
     *      @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Phones")
     */
    #[Route('/api/phones', name: 'phones', methods:['GET'], defaults:['_role' => 'customer'])]
    public function getPhoneList(PhoneRepository $phoneRepository,
    Request $request, SerializerInterface $serializer): Response
    {
        try {            
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            if ($page > 0 && $limit > 0 && $limit <= 50)
            {
                $phoneList = $phoneRepository->findAllWithPagination($page, $limit);
                $response = new Response($serializer->serialize($phoneList, 'json'),
                            Response::HTTP_OK,
                            ['Content-Type' => 'application/json']
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
     * Fetch a particular phone
     * 
     * @OA\Response(
     *     response=200,
     *     description="Returns a phone",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * @OA\Tag(name="Phones")
     */
    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'], defaults:['_role' => 'customer'])]
    public function getPhoneDetail(Request $request, Phone $phone, CacheInterface $cache, PhoneRepository $phoneRepository): Response
    {
        try {
            $phoneId = $phone->getId();
            $idCache = "PhoneDetail-". $phoneId;
            $phoneDetails = $cache->get($idCache, function (ItemInterface $item) use ($phoneId, $phoneRepository){
                $phone = $phoneRepository->find($phoneId);
                return $phone;    
            });
            return $this->json($phone, Response::HTTP_OK, []);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
