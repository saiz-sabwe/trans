<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Company;
use App\Entity\Engin;
use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\WalletOperation;
use App\Entity\WalletOperationDetail;

use App\Entity\WalletOperationItineraryDetails;
use App\Makuta\MakutaEndpointService;
use App\Repository\WalletOperationDetailRepository;
use App\Repository\WalletOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WalletOperationService
{

    private EntityManagerInterface $entityManager;
    private WalletService $walletService;
    private Security $security;
    private LoggerInterface $logger;
    private MakutaEndpointService $makutaEndpointService;
    private WalletOperationRepository $walletOperationRepository;
    private WalletOperationDetailRepository $walletOperationDetailRepository;
    private ParameterBagInterface $params;
    private ArrayService $arrayService;
    private EnginService $enginService;
    private UserService $userService;
    private EnginItineraryService $enginItineraryService;
    private ItineraryPricingService $itineraryPricingService;
    private EnginAgentService $enginAgentService;
    private WalletOperationDetailService $wods;


    public function __construct(EntityManagerInterface $entityManager,
                                WalletService $walletService,
                                Security $security,
                                LoggerInterface $logger,
                                MakutaEndpointService $makutaEndpointService,
                                ParameterBagInterface $params, ArrayService $arrayService,
                                EnginService $enginService, UserService $userService,
                                EnginItineraryService $enginItineraryService,
                                ItineraryPricingService $itineraryPricingService,
                                WalletOperationRepository $walletOperationRepository,
                                WalletOperationDetailRepository $walletOperationDetailRepository,
                                EnginAgentService $enginAgentService, WalletOperationDetailService $wods)

    {
        $this->entityManager = $entityManager;
        $this->walletService = $walletService;
        $this->security = $security;
        $this->logger = $logger;
        $this->makutaEndpointService = $makutaEndpointService;
        $this->walletOperationRepository = $walletOperationRepository;
        $this->walletOperationDetailRepository = $walletOperationDetailRepository;
        $this->params = $params;
        $this->arrayService = $arrayService;
        $this->enginService = $enginService;
        $this->userService = $userService;
        $this->enginItineraryService = $enginItineraryService;
        $this->itineraryPricingService = $itineraryPricingService;
        $this->enginAgentService = $enginAgentService;
        $this->wods = $wods;
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[ArrayShape(["message" => "string", "postAction"=>[]])]
    public function createTopup(float $amount, string $payerOperator, string $payerCurrency, string $payerAccountNumber, string $payerDescription="Recharge", string $channel = null): array
    {
        $this->logger->info("# WalletOperationService > createTopup: Start");

        //region Check connected User
        $user = $this->security->getUser();

        if (!($user instanceof User)) {
            $this->logger->info("# WalletOperationService > createTopup: User not connected", ['user' => $user]);

            throw new \RuntimeException("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        $this->logger->info("# WalletOperationService > createTopup: User connected", ['pseudo' => $user->getPseudo(), 'username' => $user->getUsername()]);
        //endregion

        //region Create Wallet
        $wallet = $user->getWallet();

        $this->logger->info("# WalletOperationService > createTopup: wallet user", ['wallet' => $wallet]);

        if (!($wallet instanceof Wallet))
        {
            $this->logger->info("# WalletOperationService > createTopup: wallet user null");

            $wallet = $this->walletService->createWallet($user);
            $this->entityManager->persist($wallet);

            $this->logger->info("# WalletOperationService > createTopup: wallet user created succesfully");
        }
        //endregion

        //region Create WalletOperation & WalletOperationDetails

        //region Create WalletOperation
        $walletOperation = new WalletOperation();


        $walletOperation->setAmount(abs($amount));


        //$walletOperation->setAmount(abs($amount));
        $walletOperation->setWallet($wallet);
        $walletOperation->setDescription($payerDescription);

        $walletOperation->setChannel(($channel === "momo")?"momo":null);

        $this->entityManager->persist($walletOperation);
        //endregion

        //region Create WalletOperationDetail
        $walletOperationDetail = new WalletOperationDetail();

        $walletOperationDetail->setPayerAccountNumber($payerAccountNumber);
        $walletOperationDetail->setPayerCurrency($payerCurrency);
        $walletOperationDetail->setPayerOperator($payerOperator);
        $walletOperationDetail->setWalletOperation($walletOperation);

        $this->entityManager->persist($walletOperationDetail);

        $this->logger->info("# WalletOperationService > createTopup: WalletOperation and WalletOperationDetail created successfully", ['walletOperationId' => $walletOperation->getId()->__toString(), 'pseudo' => $user->getPseudo(), 'username' => $user->getUsername()]);

        //endregion

        //region Initiate transaction to Makuta
        $this->logger->info("# WalletOperationService > createTopup: before Create Makuta transaction", ['walletOperationId' => $walletOperation->getId()->__toString(), 'pseudo' => $user->getPseudo(), 'username' => $user->getUsername()]);

        $result = $this->makutaEndpointService->createTransaction(
            $amount,
            $walletOperation->getDescription(),
            $walletOperation->getId(),
            $walletOperationDetail->getPayerAccountNumber(),
            $walletOperationDetail->getPayerOperator(),
            $walletOperationDetail->getPayerCurrency()
        );

        $makutaId = $result['makutaId'];
        $postAction = $result['postAction'];

        $this->logger->info("# WalletOperationService > createTopup: Message from Makuta", ['makutaId' => $makutaId]);

        $walletOperationDetail->setMakutaId($makutaId);

        $this->entityManager->persist($walletOperationDetail);
        //endregion


        //region Flush all entities
        $this->entityManager->flush();
        //endregion

        $this->logger->info("# WalletOperationService > createTopup: End Successfully");

        return [
            'makutaId' => $makutaId,
            'postAction' => $postAction
        ];
    }

    public function closeTopup(string $makutaId, int $c2bStatus): void
    {
        $this->logger->info("# WalletOperationService > closeTopup: Start", ['makutaId' => $makutaId, 'c2bStatus' => $c2bStatus]);

        //region Update walletOperation && walletOperationDetail
        $walletOperationDetail = $this->wods->findByMakutaId($makutaId);
        $walletOperation = $walletOperationDetail->getWalletOperation();

        if($walletOperation->getClosedStatus()!==null)
        {
            throw new \RuntimeException("operation déjà traitée", Response::HTTP_UNAUTHORIZED);
        }

        $walletOperation->setClosedStatus($c2bStatus);
        $walletOperation->setDateClosed(new \DateTime());

        $this->entityManager->persist($walletOperation);

        if($c2bStatus !== Response::HTTP_OK)
        {
            $this->entityManager->flush();
            throw new \RuntimeException("Paiement annulé", Response::HTTP_NOT_ACCEPTABLE);
        }
        //endregion

        //region Update Wallet

        $wallet = new Wallet();


        //TODO:to refractor

        $subscription = $walletOperation->getSubsciption();
        if($subscription instanceof Subscription){
            $subscription->setC2bStatus($c2bStatus);
            $this->entityManager->persist($subscription);
        }




        $channel = $walletOperation->getChannel();
        if($channel !== "momo")
        {

            $this->logger->info("# WalletOperationService > closeTopup: channel value", ['channel' => $channel]);

            $wallet = $walletOperation->getWallet();

            if(!($wallet instanceof Wallet))
            {
                throw new \RuntimeException("Wallet non trouvé", Response::HTTP_NOT_FOUND);
            }

            $oldBalance = $wallet->getBalance();
            $amount = $walletOperation->getAmount();
            $newBalance = $oldBalance + $amount;

            $wallet->setBalance($newBalance);

            $this->entityManager->persist($wallet);
            //endregion



            $this->logger->info("# WalletOperationService > closeTopup update balance: End Successfully", ['makutaId' => $makutaId, 'c2bStatus' => $c2bStatus, 'topupAmount' => $amount, 'oldBalance' => $oldBalance, 'newBalance' => $newBalance]);


        }

        //region Flush all entities
        $this->entityManager->flush();
        //endregion

        $this->logger->info("# WalletOperationService > closeTopup: End Successfully", ['makutaId' => $makutaId, 'c2bStatus' => $c2bStatus]);
    }

    public function payTrip(array $request, String $methode): void
    {
        $this->logger->info("# WalletOperationService > payTrip: Start");

        //region structure
        $data = $this->formatStructure($request,$methode);
        //endregion

        //region Autorisation d'accès

        $currentUser = $this->security->getUser();

        if(!($currentUser instanceof User))
        {
            throw new \RuntimeException("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        //region pin
        $pin = trim($data['pin']) ?: null;

        if ($pin === null)
        {
            $this->logger->info("# WalletOperationService > payTrip: PIN est null", ['pin' => $data['pin']]);
            throw new \RuntimeException("Pin invalide", Response::HTTP_UNAUTHORIZED);
        }

        $validPin =  $this->userService->checkPassword($currentUser, $pin);

        if(!$validPin)
        {
            $this->logger->info("# WalletOperationService > payTrip: le pin est incorrect", ['pin' => $pin]);
            throw new \RuntimeException("Code de sécurité invalide", Response::HTTP_BAD_REQUEST);
        }
        //endregion

        $currentAgent = $currentUser->getAgent();

        if(!($currentAgent instanceof Agent))
        {
            $this->logger->info("# WalletOperationService > payTrip: Current User not an Agent", ['currentUser' => $currentUser->getUsername(), 'currentAgent' => $currentAgent]);
            throw new \RuntimeException("Utilisateur non Agent", Response::HTTP_UNAUTHORIZED);
        }

        if(!$this->security->isGranted('ROLE_DRIVER'))
        {
            throw new \RuntimeException("L'agent connecté n'est pas un Chauffeur", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region Engin
        $this->logger->info("# WalletOperationService > payTrip: Before findOneByRegistrationCipher");

        $engin = $this->enginService->findOneByRegistrationCipher($data['enginCipher']);

        $this->logger->info("# WalletOperationService > payTrip: After findOneByRegistrationCipher successfully");

        if ($engin->getCompany() !== $currentAgent->getCompany())
        {
            $this->logger->info("# WalletOperationService > payTrip: engin->getCompany !== currentAgent->getCompany");

            $enginCompany = $engin->getCompany()->getLabel();
            $agentCompany = $currentAgent->getCompany() instanceof Company ? $currentAgent->getCompany()->getLabel() : null;

            $this->logger->info("# WalletOperationService > payTrip: current User not affected to the Engin company", ['currentUser' => $currentUser->getUsername(), 'enginCompany' => $enginCompany, 'agentCompany' => $agentCompany]);

            throw new \RuntimeException("Vehicule pas de la compagnie de l'utilisateur", Response::HTTP_NOT_FOUND);
        }
        $this->logger->info("# WalletOperationService > payTrip: engin->getCompany == currentAgent->getCompany");
        //endregion

        //region EnginAgent
        $enginAgent = $this->enginAgentService->findOneLastByAgent($currentAgent);

        if($engin !== $enginAgent->getEngin())
        {
            throw new \RuntimeException("Chauffeur affecté à un autre véhicule", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region Itiniraire
        $enginItinerary = $this->enginItineraryService->findOneLastByEngin($engin);
        $itinerary = $enginItinerary->getItinerary();
        $itineraryPricing = $this->itineraryPricingService->findOneLastByItinerary($itinerary);

        $itineraryAmount = $itineraryPricing->getAmount();
        //endregion

        //region Payer
        $this->logger->info("# WalletOperationService > payTrip: before get payer");
        $payer = $this->userService->findByUsername($data['payerAccountNumber']);
        $this->logger->info("# WalletOperationService > payTrip: payer got successfully");
        $payerWallet = $payer->getWallet();

        if (!($payerWallet instanceof Wallet))
        {
            $this->logger->info("# WalletOperationService > payTrip: Solde du payeur insuffisant (Aucun Wallet trouvé)");
            throw new \RuntimeException("Solde du payeur insuffisant", Response::HTTP_BAD_REQUEST);
        }

        $payerBalance = $payerWallet->getBalance();
        //endregion

        //region WalletOperation
        if($payerBalance < $itineraryAmount)
        {
            $this->logger->info("# WalletOperationService > payTrip: Solde du payeur insuffisant (Solde insuffisant)");
            throw new \RuntimeException("Solde du payeur insuffisant", Response::HTTP_UNAUTHORIZED);
        }


        $walletOperation = new WalletOperation();

        $walletOperation->setAmount(-$itineraryAmount);
        $walletOperation->setWallet($payerWallet);
        $walletOperation->setDescription($itinerary);

        $walletOperation->setClosedStatus(200);
        $walletOperation->setDateClosed( new \DateTime());

        $this->entityManager->persist($walletOperation);

////        $wallet = new Wallet();
//        $wallet->setOwner($payer);
//        $wallet->setBalance($payerBalance-$itineraryAmount);

        $payerWallet->setBalance($payerBalance-$itineraryAmount);

        $this->logger->info("# WalletOperationService > payTrip: wallet balance", ["balance"=>$payerWallet->getBalance()]);


        $this->entityManager->persist($payerWallet);

        $walletOperationItineraryDetails = new WalletOperationItineraryDetails();
        $walletOperationItineraryDetails->setWalletOperation($walletOperation);
        $walletOperationItineraryDetails->setItineraryPricing($itineraryPricing);

        $this->entityManager->persist($walletOperationItineraryDetails);

        $this->entityManager->flush();
        //endregion
    }

    public function formatStructure(array $data, string $methode): array
    {
        $structureFormatted = [];

        switch ($methode) {
            case "payerCard":
                $structure = [
                    'payerAccountNumberCipher',
                    'enginCipher',
                    'pin',
                ];

                $this->arrayService->array_diff($structure, $data);

                $payer = $this->userService->findOneByUserCipher($data['payerAccountNumberCipher']);

                $structureFormatted = [
                    'payerAccountNumber' => $payer->getUsername(),
                    'enginCipher' => $data['enginCipher'],
                    'pin' => $data['pin'],
                ];
                break;

            case "payerNumber":
                $structure = [
                    'payerAccountNumber',
                    'enginCipher',
                    'pin',
                ];
                $this->arrayService->array_diff($structure, $data);

                $structureFormatted = [
                    'payerAccountNumber' =>$data['payerAccountNumber'],
                    'enginCipher' => $data['enginCipher'],
                    'pin' => $data['pin'],
                ];
                break;

            default:
                throw new \InvalidArgumentException("Methode non supportée : $methode");
        }

        return $structureFormatted;
    }

    public function getLatestOperation(): array
    {
        $user = $this->userService->getCurrentUSer();
        $engin = new Engin();
        $u = $engin->getOwner();
        $wallet = $user->getWallet();
        if (!($wallet instanceof Wallet)) {
            return [];
        }

        $wallet_id = $wallet->getId();
        return $this->walletOperationRepository->findLastOperation($wallet_id);
    }

    public function register(array $data)
    {

        $this->logger->info("# WalletOperationService > register: start");
        $this->logger->info("# WalletOperationService > register: data received", ["data" => $data]);

        //region structure
        $structure = [
            "beginDate",
            "endDate",
            "offset",
        ];

        $this->arrayService->array_diff($structure, $data);
        //endregion

        //region benginDate and endDate

        $beginDate = trim($data['beginDate']) ?: null;

        if ($beginDate === null) {
            $this->logger->info("# WalletOperationService > register :  beginDate null");
            throw new \RuntimeException("beginDate n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $beginDate = new \DateTime($beginDate);

        $endDate = trim($data['endDate']) ?: null;

        if ($endDate === null) {
            $this->logger->info("# WalletOperationService > register : endDate null");
            throw new \RuntimeException("endDate n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $endDate = new \DateTime($endDate);

        //endregion

        //region offset
        $offset = trim($data['offset']) !== '' ? trim($data['offset']) : null;

        if ($offset === null) {
            $this->logger->info("# WalletOperationService > register : offset null ou invalide");
            throw new \RuntimeException("offset n'est pas renseigné ou invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region subscription
        $subscription = $this->findOperationByOneDates($beginDate, $endDate,10, $offset);
        //endregion

        $this->logger->info("# WalletOperationService > register : end with success");

        return $subscription;

    }

    public function findOperationByOneDates(\DateTime $beginDate, \DateTime $endDate, int $limit, int $offset)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('wo')
            ->from(WalletOperation::class, 'wo')
            ->where('wo.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('wo.dateCreated', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

}