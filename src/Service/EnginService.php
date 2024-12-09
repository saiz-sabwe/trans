<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Company;
use App\Entity\Engin;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class EnginService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private Security $security;
    private RsaService $rsaService;
    private ParameterBagInterface $bag;
    private CertificatService $certificatService;
    private ArrayService $arrayService;
    private NotificationService $notificationService;

    private UserService $userService;


    public function __construct(LoggerInterface $logger,
                                EntityManagerInterface $em,
                                Security $security,
                                RsaService $rsaService, ParameterBagInterface $bag,
                                CertificatService $certificatService,
                                ArrayService $arrayService,
                                NotificationService $notificationService,
                                UserService $userService)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->security = $security;
        $this->rsaService = $rsaService;
        $this->bag = $bag;
        $this->certificatService = $certificatService;
        $this->arrayService = $arrayService;
        $this->notificationService = $notificationService;
        $this->userService = $userService;
    }

    public function create(array $data)
    {
        $this->logger->info("# EnginService > create: Start", ['data' => $data]);

        $structure = [
            'pseudo',
            'phoneNumber',
            'registration',
            'pin',
            'otp'
        ];

        $this->arrayService->array_diff($structure, $data);

        //region Pseudo
        $pseudo = trim($data['pseudo']) ?: null;

        if ($pseudo === null)
        {
            throw new \RuntimeException("Pseudo invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region phoneNumber
        $phoneNumber = trim($data['phoneNumber']) ?: null;

        if ($phoneNumber === null)
        {
            throw new \RuntimeException("No.Téléphone invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region registration
        $registration = trim($data['registration']) ?: null;

        if ($registration === null)
        {
            throw new \RuntimeException("No.Plaque invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region pin
        $pin= trim($data['pin']) ?: null;

        if ($pin === null)
        {
            throw new \RuntimeException("Code PIN invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region otp
        $otp = trim($data['otp']) ?: null;

        if ($otp === null)
        {
            throw new \RuntimeException("OTP invalide", Response::HTTP_UNAUTHORIZED);
        }

        $this->notificationService->verifyOtp($phoneNumber, $otp);
        //endregion

        //region Persister la BD
        $engin = $this->findOneByRegistration($registration, true, false);

        $this->logger->info('# EnginService > create :engin', ['engin'=>$engin]);


        if (!($engin instanceof Engin))
        {
            $this->logger->info('# EnginService > create :engin not founded');

            $owner = $this->userService->findByUsername($phoneNumber, false);

            if (!($owner instanceof User))
            {
                $owner = $this->userService->create($phoneNumber, $pseudo, $pin, true);
                $this->em->persist($owner);
            }
            else
            {
                $isVerified = $this->userService->checkPassword($owner, $pin);

                if(!$isVerified)
                {
                    throw new \RuntimeException("PIN du propriétaire invalide", Response::HTTP_UNAUTHORIZED);
                }
            }

            $engin = new Engin();
            $engin->setRegistration($registration);
            $engin->setOwner($owner);
            $engin->setRegistrationCipher("Créer un nouveau Cipher");

            $this->em->persist($engin);
        }
        else
        {
            $this->logger->info('# EnginService > create :engin founded');
            if($engin->getOwner() instanceof User)
            {
                $this->logger->info('# EnginService > create :engin has user');
                $message = $engin->getOwner()->getUsername() === $phoneNumber
                    ? "Ce véhicule vous est déjà attribué"
                    : "Véhicule déjà attribué à un autre client";

                throw new \RuntimeException($message, Response::HTTP_UNAUTHORIZED);
            }
            else
            {
                $this->logger->info('# EnginService > create :user not found');
                $owner = $this->userService->findByUsername($phoneNumber, false);

                if (!($owner instanceof User))
                {
                    $owner = $this->userService->create($phoneNumber, $pseudo, $pin, true);
                    $this->em->persist($owner);
                }
                else
                {
                    $this->logger->info('# EnginService > create :user found');
                    $isVerified = $this->userService->checkPassword($owner, $pin);

                    if(!$isVerified)
                    {
                        throw new \RuntimeException("PIN du propriétaire invalide", Response::HTTP_UNAUTHORIZED);
                    }

                    $engin->setOwner($owner);
                    //TODO:creer un nouveau cypher
                    $engin->setRegistrationCipher("Créer un nouveau Cipher");

                    $this->em->persist($engin);
                }
            }

        }

        $this->logger->info('# EnginService > create :end create engin with success');

        $this->em->flush();
        //endregion

        return $engin;
    }

    public function createByRegistration(String $registration): Engin
    {

        $this->logger->info("# EnginService > createByRegistration: Start");

        $this->logger->info("# EnginService > createByRegistration  : registration received : ", ["registration" => $registration]);

        $engin = new Engin();

        $engin->setRegistration($registration);

        $this->em->persist($engin);
        $this->em->flush();

        return $engin;
    }


    public function getCipherRegistration(string $registration): string
    {
        $this->logger->info("# EnginService > getCipherRegistration: Start");

        $currentUser = $this->security->getUser();

        if (!($currentUser instanceof User)) {
            throw new \RuntimeException("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        $currentAgent = $currentUser->getAgent();

        if (!($currentAgent instanceof Agent)) {
            $this->logger->info("# EnginService > getCipherRegistration: Current User not an Agent", ['currentUser' => $currentUser->getUsername(), 'currentAgent' => $currentAgent]);
            throw new \RuntimeException("Utilisateur non Agent", Response::HTTP_UNAUTHORIZED);
        }

        $engin = $this->findOneByRegistration($registration);

        if ($engin->getCompany() !== $currentAgent->getCompany()) {

            $enginCompany = $engin->getCompany()->getLabel();
            $agentCompany = $currentAgent->getCompany() instanceof Company ? $currentAgent->getCompany()->getLabel() : null;

            $this->logger->info("# EnginService > getCipherRegistration: current User not affected to the Engin company", ['currentUser' => $currentUser->getUsername(), 'enginCompany' => $enginCompany, 'agentCompany' => $agentCompany]);

            throw new \RuntimeException("Vehicule pas de la compagnie de l'utilisateur", Response::HTTP_NOT_FOUND);
        }

        return $this->rsaService->encrypt($this->certificatService->getPublicKey(), $engin->getRegistration());
    }

    public function getRegistrationByCipher(string $cipher): string
    {
        $this->logger->info("# EnginService > getRegistrationByCipher: Start");
        return $this->rsaService->decrypt($this->certificatService->getPrivateKey(), $cipher, $this->bag->get('lexik_jwt_authentication')['passphrase']);
    }

    public function findOneByRegistration(string $registration, bool $withCipher = false, bool $throw = true)
    {
        $this->logger->info("# EnginService > findOneByRegistration: Start", ["registration" => $registration]);

        $engin = $this->em->getRepository(Engin::class)->findOneBy([
            'registration' => $registration,
            'isWorking' => true
        ]);

        if ($engin === null) {
            $engin = $this->createByRegistration($registration);
        }

        if (!($engin instanceof Engin) && $throw) {
            throw new \RuntimeException("Véhicule non trouvé", Response::HTTP_UNAUTHORIZED);
        }

        //if ($engin->getCompany()->isDeleted()) {
        //    throw new \RuntimeException("Compagnie inactive", Response::HTTP_UNAUTHORIZED);
        //}
        if ($engin !== null && ($engin->getOwner() instanceof User))
        {
            $engin->setRegistrationCipher($engin->getRegistration());
        }

        return $engin;
    }

    public function findOneByPhone(string $registration)
    {
        $this->logger->info("# EnginService > findOneByRegistration: Start");

        $this->logger->info("# EnginService > findOneByRegistration  : registration received : ", ["registration" => $registration]);

        $engin = $this->em->getRepository(Engin::class)->findOneBy([
            'registration' => $registration,
            'isWorking' => true
        ]);

        if (!($engin instanceof Engin)) {
            throw new \RuntimeException("Véhicule non trouvé", Response::HTTP_UNAUTHORIZED);
        }

        if ($engin->getCompany()->isDeleted()) {
            throw new \RuntimeException("Compagnie inactive", Response::HTTP_UNAUTHORIZED);
        }

        return $engin;
    }

    public function findOneByRegistrationCipher(string $cipher)
    {
        $this->logger->info("# EnginService > findOneByRegistrationCipher: Start");
        $registration = $this->getRegistrationByCipher($cipher);
        return $this->findOneByRegistration($registration);
    }
}