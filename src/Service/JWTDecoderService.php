<?php

// src/Service/JWTDecoderService.php
namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTDecoderService
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * Decode a JWT token from the Authorization header.
     *
     * @param string|null $bearerToken
     * @return array|null
     */
    public function decodeBearerToken(?string $bearerToken): ?array
    {
        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            throw new AuthenticationException('Missing or malformed Authorization header.');
        }

        $token = substr($bearerToken, 7); // Remove "Bearer " prefix

        try {
            return $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid JWT token.');
        }
    }

    /**
     * Extract the username from the decoded JWT token.
     *
     * @param string|null $bearerToken
     * @return string|null
     */
    public function getUsernameFromBearerToken(?string $bearerToken): ?string
    {
        $decodedToken = $this->decodeBearerToken($bearerToken);

        return $decodedToken['username'] ?? null;
    }
}
