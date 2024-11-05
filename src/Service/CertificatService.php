<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class CertificatService
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getPrivateKey(): string
    {
        $private = $this->kernel->getProjectDir() . '/config/jwt/private.pem';

        if (!file_exists($private)) {
            throw new \RuntimeException("Clé privée non trouvé", Response::HTTP_NOT_FOUND);
        }

        return file_get_contents($private);
    }

    public function getPublicKey(): string
    {
        $public = $this->kernel->getProjectDir() . '/config/jwt/public.pem';

        if (!file_exists($public)) {
            throw new \RuntimeException("Clé public non trouvée", Response::HTTP_NOT_FOUND);
        }

        return file_get_contents($public);
    }
}