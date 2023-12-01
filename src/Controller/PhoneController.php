<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods:['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository): Response
    {
        $phoneList = $phoneRepository->findAll();

        return $this->json($phoneList, Response::HTTP_OK, []);
    }

    #[Route('/api/phones/{id}', name: 'detailPhone', methods:['GET'])]
    public function getPhoneDetail(Phone $phone): Response
    {
        return $this->json($phone, Response::HTTP_OK, []);
    }
}
