<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods:['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, Request $request): Response
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $phoneList = $phoneRepository->findAllWithPagination($page, $limit);

        return $this->json($phoneList, Response::HTTP_OK, []);
    }

    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'])]
    public function getPhoneDetail(Phone $phone): Response
    {
        return $this->json($phone, Response::HTTP_OK, []);
    }
}
