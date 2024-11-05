<?php

namespace App\Service;

use App\Entity\Engin;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class CardAllocationService
{
    private LoggerInterface $logger;
    private ArrayService $arrayService;
    private UserService $userService;
    private EnginService $enginService;
    private RsaService $rsaService;
    private CertificatService $certificatService;
    private NotificationService $notificationService;
    private Security $security;

    public function __construct(LoggerInterface $logger, ArrayService $arrayService, UserService $userService, RsaService $rsaService, CertificatService $certificatService,  NotificationService $notificationService, EnginService $enginService, Security $security)
    {
        $this->logger = $logger;
        $this->arrayService = $arrayService;
        $this->userService = $userService;
        $this->rsaService = $rsaService;
        $this->certificatService = $certificatService;
        $this->enginService = $enginService;
        $this->notificationService = $notificationService;
        $this->security = $security;
    }

    public function allocate(array $data, bool $isToCrypte=true): string
    {
        $this->logger->info("# CardAllocationService > allocate: Start", ['receivedData' => $data]);

        $structure = [
            'pseudo',
            'phoneNumber',
            'otp'
        ];

        $this->arrayService->array_diff($structure, $data);

        //region Pseudo
        $pseudo = trim($data['pseudo']) ?: null;

        if ($pseudo === null)
        {
            $this->logger->info("# CardAllocationService > allocate: Le pseudo est null", ['pseudo' => $data['pseudo']]);
            throw new \RuntimeException("Pseudo invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region PhoneNumber
        $phoneNumber = trim($data['phoneNumber']) ?: null;

        if ($phoneNumber === null)
        {
            $this->logger->info("# CardAllocationService > allocate: Le phoneNumber est null", ['phoneNumber' => $data['phoneNumber']]);
            throw new \RuntimeException("PhoneNumber invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region OTP
        $otp = trim($data['otp']) ?: null;

        if ($otp === null)
        {
            $this->logger->info("# CardAllocationService > allocate: L'OTP est null", ['otp' => $data['otp']]);
            throw new \RuntimeException("OTP invalide", Response::HTTP_UNAUTHORIZED);
        }

//        $this->notificationService->verifyOtp($phoneNumber, $otp);

        if($otp !== "1234")
        {
            $this->logger->info("# CardAllocationService > allocate: L'OTP est invalide à la verification", ['otp' => $otp]);
            throw new \RuntimeException("Code de sécurité invalide", Response::HTTP_BAD_REQUEST);
        }
        //endregion

        $this->logger->info("# CardAllocationService > allocate: Tentative de créer l'utilisateur", ['phoneNumber' => $phoneNumber]);

        $user = $this->userService->findByUsername($phoneNumber, false);

        if(!($user instanceof User))
        {
            $this->logger->info("# CardAllocationService > allocate: Before Create new user", ['phoneNumber' => $phoneNumber]);
            $user = $this->userService->create($phoneNumber, $pseudo, "1234", true,true);
            $this->logger->info("# CardAllocationService > allocate: After Create new user successfully", ['phoneNumber' => $phoneNumber]);
        }

        $this->logger->info("# CardAllocationService > allocate: Encrypte cardNumber", ['phoneNumber' => $phoneNumber]);

        if($isToCrypte){
            return $this->rsaService->encrypt($this->certificatService->getPublicKey(), $user->getUsername());
        }else{
            return $user->getUsername();
        }

    }

    public function allocateRegistration(array $data): string
    {
        $this->logger->info("# CardAllocationService > allocate: Start", ['receivedData' => $data]);

        //region structure
        $structure = [
            'registration',
            'pin'
        ];

        $this->arrayService->array_diff($structure, $data);

        $pin = trim($data['pin']) ?: null;

        if ($pin === null)
        {
            $this->logger->info("# SubscriptionService > payTrip: PIN est null", ['pin' => $data['pin']]);
            throw new \RuntimeException("Pin invalide", Response::HTTP_UNAUTHORIZED);
        }


        $registration = trim($data['registration']) ?: null;

        if ($registration === null)
        {
            $this->logger->info("# CardAllocationService > allocateRegistration: registration est null", ['registration' => $data['registration']]);
            throw new \RuntimeException("registration pas renseigné", Response::HTTP_UNAUTHORIZED);
        }
        //endregion


        //region pin
        $currentUser = $this->security->getUser();

        if(!($currentUser instanceof User))
        {
            throw new \RuntimeException("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        $validPin =  $this->userService->checkPassword($currentUser, $pin);

        if(!$validPin)
        {
            $this->logger->info("# WalletOperationService > payTrip: le pin est incorrect", ['pin' => $pin]);
            throw new \RuntimeException("Code de sécurité invalide", Response::HTTP_BAD_REQUEST);
        }
        //endregion

        //region engin
        $engine = $this->enginService->findOneByRegistration($registration);
        //endregion

        return $engine->getRegistration();


    }
}