<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PhoneController extends AbstractController
{
    private $jwtManager;
    private $tokenStorageInterface;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }
    
    #[Route('/api/phones', name: 'phones', methods:['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, Request $request): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                //$customerMail = $decodedToken['username'];
                if ($decodedToken)
                {
                    $page = $request->get('page', 1);
                    $limit = $request->get('limit', 5);
                    $phoneList = $phoneRepository->findAllWithPagination($page, $limit);

                    return $this->json($phoneList, Response::HTTP_OK, []);
                }
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Erreur relative au token'], Response::HTTP_UNAUTHORIZED);
            }
        }


    }

    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'])]
    public function getPhoneDetail(Request $request, Phone $phone): Response
    {
        $token = $request->headers->get('Authorization');
        if ($token && str_starts_with($token, 'bearer'))
        {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
                //$customerMail = $decodedToken['username'];
                if ($decodedToken)
                {
                    return $this->json($phone, Response::HTTP_OK, []);
                }
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Erreur relative au token'], Response::HTTP_UNAUTHORIZED);
            }
        }
    }
}
