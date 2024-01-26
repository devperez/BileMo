<?php
namespace App\Service;

use App\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Service for handling JWT tokens and extracting customer information.
 *
 * This service provides methods to retrieve customer information from a JWT token
 * in the Authorization header of an HTTP request.
 *
 */
class JwtTokenService
{
    /**
     * @var JWTTokenManagerInterface The JWT token manager.
     */
    private $jwtManager;

    /**
     * @var TokenStorageInterface The token storage.
     */
    private $tokenStorage;

    /**
     * JwtTokenService constructor.
     *
     * @param JWTTokenManagerInterface $jwtManager   The JWT token manager.
     * @param TokenStorageInterface    $tokenStorage The token storage.
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get the customer ID from the provided JWT token in the request.
     *
     * @param Request               $request             The HTTP request.
     * @param CustomerRepository    $customerRepository The customer repository.
     *
     * @return string The customer ID.
     *
     * @throws AuthenticationException If there is an error related to the token or authentication.
     */
    public function getCustomerIdFromRequest(Request $request, CustomerRepository $customerRepository)
    {
        $token = $request->headers->get('Authorization');

        if ($token && str_starts_with($token, 'bearer')) {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorage->getToken());
                $customerMail = $decodedToken['username'];
                $customer = $customerRepository->findOneBy([
                    'email' => $customerMail,
                ]);
                $customerId = $customer->getId();
                return $customerId;
            } catch (\Exception $e) {
                throw new AuthenticationException('Erreur relative au token');
            }
        }

        throw new AuthenticationException('Token non fourni ou invalide');
    }
}