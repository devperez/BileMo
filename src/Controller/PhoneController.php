<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\JwtTokenService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    private $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }
    
    #[Route('/api/phones', name: 'phones', methods:['GET'], defaults:['_role' => 'customer'])]
    public function getPhoneList(PhoneRepository $phoneRepository,
    Request $request): Response
    {
        try {
            //$customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $phoneList = $phoneRepository->findAllWithPagination($page, $limit);

            return $this->json($phoneList, Response::HTTP_OK, []);                
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    
    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'], defaults:['_role' => 'customer'])]
    public function getPhoneDetail(Request $request, Phone $phone): Response
    {
        try {
            // $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            return $this->json($phone, Response::HTTP_OK, []);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
