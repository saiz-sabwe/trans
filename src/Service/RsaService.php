<?php

namespace App\Service;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class RsaService
{
    public function createKeys(): array
    {
        $rsa = RSA::createKey(2048);

        //$passphrase = bin2hex(random_bytes(16)); // Exemple : 32 caractères hexadécimaux
        //'privateKey' => $rsa->withPassword($passphrase)->toString('PKCS8'),
        return [
            'privateKey' => $rsa->toString('PKCS8'),
            'publicKey' => $rsa->getPublicKey()->toString('PKCS8')
        ];
    }

    public function encrypt(string $publicKey, string $plaintext): string
    {
        $public = PublicKeyLoader::load($publicKey);

        // Ensure the key is of the correct type
        if (!($public instanceof RSA\PublicKey)) {
            throw new \InvalidArgumentException('Certificat Public invalide.');
        }

        $ciphertext = $public->encrypt($plaintext);

        return base64_encode($ciphertext);
    }

    public function decrypt(string $privateKey, string $ciphertext, string $passphrase = null): string
    {
        $private = PublicKeyLoader::loadPrivateKey($privateKey, $passphrase);

        // Ensure the key is of the correct type
        if (!($private instanceof RSA\PrivateKey)) {
            throw new \InvalidArgumentException('Certificat Privé invalide.');
        }

        return $private->decrypt(base64_decode($ciphertext));
    }
}