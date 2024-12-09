<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserService
{

    private EntityManagerInterface $em;
    private Security $security;
    private UserPasswordHasherInterface $userPasswordHasher;
    private LoggerInterface $logger;
    private RsaService $rsaService;
    private ParameterBagInterface $bag;
    private CertificatService $certificatService;

    public function __construct(EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $userPasswordHasher, LoggerInterface $logger, RsaService $rsaService, ParameterBagInterface $bag, CertificatService $certificatService)
    {
        $this->em = $em;
        $this->security = $security;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->logger = $logger;
        $this->rsaService = $rsaService;
        $this->bag = $bag;
        $this->certificatService = $certificatService;
    }

    public function findByUsername(string $username, bool $throw = true)
    {
        $this->logger->info("# UserService > findByUsername: Start");
        $this->logger->info("# UserService > findByUsername: data received ",['username' => $username]);
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!($user instanceof User) && $throw)
        {
            throw new \RuntimeException("Utilisateur non trouvé", Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    public function getCurrentUSer()
    {
        $user = $this->security->getUser();

        if (!($user instanceof User))
        {
            throw new \RuntimeException("Utilisateur non trouvé", Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    public function create(string $phoneNumber, string $pseudo, string $plainPassword, bool $isVerified, bool $isPersist)
    {
        $user = new User();
        $user->setUsername($phoneNumber);
        $user->setPseudo($pseudo);
        $user->setVerified($isVerified);

        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        if($isPersist)
        {
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function checkPassword(User $user, string $plainPassword): bool
    {
        // Vérifie si le mot de passe en clair correspond au mot de passe hashé de l'utilisateur
        return $this->userPasswordHasher->isPasswordValid($user, $plainPassword);
    }

    public function findOneByUserCipher(string $cipher): User
    {
        $this->logger->info("# EnginService > checkPassword: Start");
        $username = $this->getUsernameByCipher($cipher);
        return $this->findOneByUser($username);
    }

    public function getUsernameByCipher(string $cipher): string
    {
        $this->logger->info("# UserService > checkPassword: Start");
        return $this->rsaService->decrypt($this->certificatService->getPrivateKey(), $cipher, $this->bag->get('lexik_jwt_authentication')['passphrase']);
    }

    public function findOneByUser(string $username): User
    {
        $this->logger->info("# UserService > findOneByRegistration: Start");

        $user = $this->em->getRepository(User::class)->findOneBy([
            'username' => $username
        ]);

        if (!($user instanceof User))
        {
            throw new \RuntimeException("user non trouvé", Response::HTTP_UNAUTHORIZED);
        }

        return $user;
    }


}