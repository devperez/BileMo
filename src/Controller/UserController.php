<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function deleteUser(User $user, EntityManagerInterface $emi): Response
    {
        $emi->remove($user);
        $emi->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT, []);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods:['GET'])]
    public function getPhoneDetail(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, []);
    }

    #[Route('api/users', name:"createUser", methods:['POST'])]
    public function createUser(Request $request,
    SerializerInterface $serializer,
    EntityManagerInterface $emi,
    UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $customer = $emi->getRepository(Customer::class)->find('6495c094-614e-4162-8dbb-b8f60197e55d');
        $user->setCustomer($customer);

        $emi->persist($user);
        $emi->flush();

        $jsonUser = $serializer->serialize($user,'json',['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
