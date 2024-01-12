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

class PhoneController extends AbstractController
{
    private $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }
    
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
                //return $this->json($phoneList, Response::HTTP_OK, []);
                $jsonContent = $serializer->serialize($phoneList, 'json');
                $response = new Response($jsonContent, Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                return new Response('Le paramètre limit doit être un entier positif et inférieur à 51.');
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    
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
