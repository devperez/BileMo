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

class PhoneController extends AbstractController
{
    private $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }
    
    #[Route('/api/phones', name: 'phones', methods:['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, Request $request): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            if ($customerMail)
            {
                $page = $request->get('page', 1);
                $limit = $request->get('limit', 5);
                $phoneList = $phoneRepository->findAllWithPagination($page, $limit);
                
                return $this->json($phoneList, Response::HTTP_OK, []);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    
    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'])]
    public function getPhoneDetail(Request $request, Phone $phone): Response
    {
        try {
            $customerMail = $this->jwtTokenService->getCustomerMailFromRequest($request);
            if ($customerMail)
            {
                return $this->json($phone, Response::HTTP_OK, []);
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
