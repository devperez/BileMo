<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtTokenService
{
    private $jwtManager;
    private $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getCustomerMailFromRequest(Request $request)
    {
        $token = $request->headers->get('Authorization');

        if ($token && str_starts_with($token, 'bearer')) {
            try {
                $decodedToken = $this->jwtManager->decode($this->tokenStorage->getToken());
                return $decodedToken['username'];
            } catch (\Exception $e) {
                throw new AuthenticationException('Erreur relative au token');
            }
        }

        throw new AuthenticationException('Token non fourni ou invalide');
    }
}