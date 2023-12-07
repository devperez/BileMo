<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;


class UserController extends AbstractController
{
    #[Route('/api/users/customers/{id}', name: 'CustomerUserList', methods: ['GET'])]
    public function getCustomerUserList(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
        $users = $customer->getUsers();
        $jsonUsersList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    #[Route('api/users/{id}', name:'deleteUser', methods:['DELETE'])]
    public function deleteUser(Uuid $id, EntityManagerInterface $emi): Response
    {
        $user = $emi->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(null, Response::HTTP_NOT_FOUND, []);
        }
        $emi->remove($user);
        $emi->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT, []);
    }
}
