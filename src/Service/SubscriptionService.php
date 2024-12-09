<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Engin;
use App\Entity\ParkingHistory;
use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\WalletOperation;
use App\Entity\WalletOperationDetail;
use App\Makuta\MakutaEndpointService;
use App\Repository\SubscriptionRepository;
use App\Repository\WalletOperationDetailRepository;
use App\Repository\WalletOperationRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionService
{


    private EntityManagerInterface $entityManager;
    private Security $security;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;
    private ArrayService $arrayService;
    private SubscriptionPricingService $subscriptionPricingService;
    private EnginService $enginService;
    private MakutaEndpointService $makutaEndpointService;
    private SubscriptionRepository $subscriptionRepository;
    private UserService $userService;
    private ParkingService $parkingService;


    public function __construct(
        EntityManagerInterface     $entityManager,
        Security                   $security,
        LoggerInterface            $logger,
        ParameterBagInterface      $params,
        ArrayService               $arrayService,
        SubscriptionPricingService $subscriptionPricingService,
        EnginService               $enginService,
        MakutaEndpointService      $makutaEndpointService,
        SubscriptionRepository     $subscriptionRepository,
        UserService $userService,
        ParkingService $parkingService
    )

    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->params = $params;
        $this->arrayService = $arrayService;
        $this->subscriptionPricingService = $subscriptionPricingService;
        $this->enginService = $enginService;
        $this->makutaEndpointService = $makutaEndpointService;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userService = $userService;
        $this->parkingService = $parkingService;
    }

    public function create(array $data,string $channel = null): array
    {
        $this->logger->info("# SubscriptionService > createSubscribe: Start");

        $this->logger->info("# SubscriptionService > create  : data received : ", ["data" => $data]);

        //region check structure
        $structure = [
            'totalDay',
            'registration',
            'payerAccountNumber',
            'payerOperator',
            'payerCurrency'
        ];

        $this->arrayService->array_diff($structure, $data);
        //endregion

        //region subscription pricing

//        $totalDay = trim($data['totalDay']) ?: null;
        $totalDay = trim($data['totalDay']) !== '' ? trim($data['totalDay']) : null;

        if ($totalDay === null) {
            $this->logger->info("# SubscriptionService > createSubscribe: totalDay est null", ['totalDay' => $totalDay]);
            throw new \RuntimeException("totalDay n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $subscriptionPricing = $this->subscriptionPricingService->findOneBySubscriptionPricingTotalDay($totalDay);
        //endregion

        //region registration
        $registration = trim($data['registration']) ?: null;

        if ($registration === null) {
            $this->logger->info("# SubscriptionService > createSubscribe: registration est null", ['registration' => $data['registration']]);
            throw new \RuntimeException("la plaque d'immatriculation n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $engin = New Engin();

        if($totalDay == 0){
            $this->logger->info("# SubscriptionService > createSubscribe: totalDay est null", ['totalDay' => $totalDay]);
            $engin = $this->enginService->findOneByRegistration($registration);
            if(!($engin instanceof Engin)){
                $engin = $this->enginService->createByRegistration($registration) ;
            }

        }else{
            $this->logger->info("# SubscriptionService > createSubscribe: totalDay est diff de null", ['totalDay' => $totalDay]);
            $engin = $this->enginService->findOneByRegistration($registration);
        }

//        $engin = ($totalDay == 0) ? $this->enginService->createByRegistration($registration) : $this->enginService->findOneByRegistration($registration);
        //endregion

        //region Create Subscription
        $subscription = new Subscription();

        $subscription->setEngin($engin);
        $subscription->setSubscriptionPricing($subscriptionPricing);

        $dateBegin = new \DateTime();
        $dateEnd = new \DateTime();


        $subscriptionCategory = $subscriptionPricing->getSubscriptionCategoy();

        if ($subscriptionCategory->getApiKey() === "MK_CUSTOM") {
            $this->logger->info("# SubscriptionService > createSubscribe: subscriptionCategoryApiKey", ['subscriptionCategoryApiKey' => $subscriptionCategory->getApiKey()]);

            $dateEnd->modify('+' . $totalDay . ' days');
        }

        $subscription->setDateBegin($dateBegin);
        $subscription->setDateEnd($dateEnd);

        $this->entityManager->persist($subscription);

        $this->logger->info("# SubscriptionService > createSubscribe: Subscription created successfully");
        //endregion

        //region Create SubscriptionDetail

        $payerAccountNumber = trim($data['payerAccountNumber']) ?: null;

        if ($payerAccountNumber === null) {
            $this->logger->info("# SubscriptionService > createSubscribe: payerAccountNumber est null", ['payerAccountNumber' => $data['payerAccountNumber']]);
            throw new \RuntimeException("payerAccountNumber n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $payerOperator = trim($data['payerOperator']) ?: null;

        if ($payerOperator === null) {
            $this->logger->info("# SubscriptionService > createSubscribe: payerOperator est null", ['payerOperator' => $data['payerOperator']]);
            throw new \RuntimeException("payerOperator n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $payerCurrency = trim($data['payerCurrency']) ?: null;

        if ($payerCurrency === null) {
            $this->logger->info("# SubscriptionService > createSubscribe: payerCurrency est null", ['payerCurrency' => $data['payerCurrency']]);
            throw new \RuntimeException("payerCurrency n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }


        //region Create WalletOperation
        $walletOperation = new WalletOperation();

        $amount = $subscriptionPricing->getAmount();

        $walletOperation->setSubsciption($subscription);
        $walletOperation->setAmount(abs($amount));
        $walletOperation->setDescription("Subscribe " . $subscriptionCategory->getLabel());
        $walletOperation->setChannel(($channel === "momo")?"momo":null);


        $this->entityManager->persist($walletOperation);

        $this->logger->info("# SubscriptionService > createSubscribe: WalletOperation created successfully");

        //endregion

        //region Create WalletOperationDetail
        $walletOperationDetail = new WalletOperationDetail();

        $walletOperationDetail->setWalletOperation($walletOperation);
        $walletOperationDetail->setPayerAccountNumber($payerAccountNumber);
        $walletOperationDetail->setPayerCurrency($payerCurrency);
        $walletOperationDetail->setPayerOperator($payerOperator);

        $this->entityManager->persist($walletOperationDetail);

        $this->logger->info("# SubscriptionService > createSubscribe: WalletOperationDetail created successfully");

        //endregion

        //region initiate makuta transaction

        $this->logger->info("# SubscriptionService > createSubscribe: before Create Makuta transaction");

        $result = $this->makutaEndpointService->createTransaction(
            $subscriptionPricing->getAmount(),
            "Subscribe " . $subscriptionCategory->getLabel(),
            $walletOperationDetail->getId(),
            $walletOperationDetail->getPayerAccountNumber(),
            $walletOperationDetail->getPayerOperator(),
            $walletOperationDetail->getPayerCurrency()
        );


        $makutaId = $result['makutaId'];
        $postAction = $result['postAction'];

        $this->logger->info("# SubscriptionService > createSubscribe: Message from Makuta", ['makutaId' => $makutaId]);

        $walletOperationDetail->setMakutaId($makutaId);

        $this->entityManager->persist($walletOperationDetail);
        //endregion

        //region Flush all entities
        $this->entityManager->flush();
        //endregion

        $this->logger->info("# SubscriptionService > createSubscribe: End Successfully");

        return [
            'makutaId' => $makutaId,
            'postAction' => $postAction
        ];

    }

    /**
     * @throws Exception
     */
    public function checkSubscribe(array $data)
    {
        $this->logger->info("# SubscriptionService > checkSubscribe: Start", ["dataReceived" => $data]);

        //region structure
        $structure = [
//            'pin',
            'registration',

        ];

        $this->arrayService->array_diff($structure, $data);
        //endregion

/*        //region PIN
        $pin = trim($data['pin']) ?: null;

        if ($pin === null)
        {
            $this->logger->info("# SubscriptionService > checkSubscribe: PIN est invalide");
            throw new \RuntimeException("Pin invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion*/

        //region Registration
        $registration = trim($data['registration']) ?: null;

        if ($registration === null) {
            $this->logger->info("# SubscriptionService > checkSubscribe: Registration invalide", ['registration' => $registration]);
            throw new \RuntimeException("No.Plaque non renseigné", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        $subscription = $this->entityManager->getRepository(Subscription::class)->findOneLastByRegistration($registration);

        $this->logger->info("# SubscriptionService > checkSubscribe: subscription", ["subscription" => $subscription]);

        if(!($subscription instanceof Subscription))
        {
            $this->logger->info("# SubscriptionService > checkSubscribe: subscription non trouvé");
            throw new \RuntimeException("Aucun abonnement trouvé", Response::HTTP_NOT_FOUND);
        }

        $owner = $subscription->getEngin()->getOwner();

        if(!($owner instanceof User))
        {
            throw new \RuntimeException("Véhicule non affecté à un utilisateur", Response::HTTP_UNAUTHORIZED);
        }

//        if (!$this->userService->checkPassword($owner, $pin))
//        {
//            throw new \RuntimeException("Code PIN invalide", Response::HTTP_UNAUTHORIZED);
//        }

        $now = new \DateTime();

        if ($subscription->getDateEnd() < $now)
        {
            throw new \RuntimeException("Votre abonnement a expiré depuis le " . $subscription->getDateEnd()->format('Y-m-d H:i:s'), Response::HTTP_UNAUTHORIZED);
        }

        $this->logger->info("# SubscriptionService > checkSubscribe: check subscription ends with success");

        return [
            'registration' => $registration,
            'message'=> "Votre abonnement est valide jusqu'au ".  $subscription->getDateEnd()->format('Y-m-d H:i:s')
        ];
    }


    public function detailSubscribe(array $data)
    {

        $this->logger->info("# SubscriptionService > detailSubscribe: start");
        $this->logger->info("# SubscriptionService > register: data received", ["data" => $data]);

        $structure = [
            'totalDay',
        ];

        $this->arrayService->array_diff($structure, $data);

        $totalDay = trim($data['totalDay']) !== '' ? trim($data['totalDay']) : null;

        if ($totalDay === null) {
            $this->logger->info("# SubscriptionService > detailSubscribe: start");
            throw new \RuntimeException("totalDay n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $subscribePricing = $this->subscriptionPricingService->findOneBySubscriptionPricingTotalDay($totalDay);

        $this->logger->info("# SubscriptionService > checkSubscribe: subscribePricing", ["subscribePricing" => $subscribePricing]);

        return $subscribePricing;

    }

    public function register(array $data)
    {

        $this->logger->info("# SubscriptionService > register: start");
        $this->logger->info("# SubscriptionService > register: data received", ["data" => $data]);

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
            $this->logger->info("# SubscriptionService > register :  beginDate null");
            throw new \RuntimeException("beginDate n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $beginDate = new \DateTime($beginDate);

        $endDate = trim($data['endDate']) ?: null;

        if ($endDate === null) {
            $this->logger->info("# SubscriptionService > register : endDate null");
            throw new \RuntimeException("endDate n'est pas renseigné", Response::HTTP_UNAUTHORIZED);
        }

        $endDate = new \DateTime($endDate);

        //endregion

        //region offset
        $offset = trim($data['offset']) !== '' ? trim($data['offset']) : null;

        if ($offset === null) {
            $this->logger->info("# SubscriptionService > register : offset null ou invalide");
            throw new \RuntimeException("offset n'est pas renseigné ou invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region subscription
        $subscription = $this->findSubscriptionByOneDates($beginDate, $endDate,10, $offset);
        //endregion

        $this->logger->info("# SubscriptionService > register : end with success");

        return $subscription;

    }

    public function findSubscriptionByOneDates(\DateTime $beginDate, \DateTime $endDate, int $limit, int $offset)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Subscription::class, 's')
            ->where('s.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('s.dateCreated', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findLastSubscriptions(int $max, object $user): array
    {

        $queryBuilders = $this->entityManager->createQueryBuilder();

        $queryBuilders->select('e')
            ->from(Engin::class, 'e')
            ->where('e.owner = :user')
            ->setParameter('user', $user->getId(), 'uuid')
            ->orderBy('e.dateCreated', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

// Fetch the latest Engin object
        $en = $queryBuilders->getQuery()->getResult();

// Check if the result is not empty
        if (!empty($en)) {
            $engin = $en[0]; // Get the first result (since setMaxResults(1) is used)

            // Create a new query for Subscription
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder->select('s')
                ->from(Subscription::class, 's')
                ->where('s.engin = :engin_id')
                ->setParameter('engin_id', $engin->getId(), 'uuid') // Use $engin here
                ->orderBy('s.dateCreated', 'DESC')
                ->setMaxResults($max);

            return $queryBuilder->getQuery()->getResult();
        } else {
            // Handle case where no Engin object is found
            return [];
        }


    }

    public function findLastSubscription(int $max, object $user): array
    {
        // Create the query builder
        $queryBuilder = $this->entityManager->createQueryBuilder();

        // Build the query with a JOIN between Engin and Subscription
        $queryBuilder->select('s')
            ->from(Subscription::class, 's')
            ->innerJoin(Engin::class, 'e', 'WITH', 's.engin = e.id')
            ->where('e.owner = :user')
            ->setParameter('user', $user->getId(), 'uuid')
            ->orderBy('s.dateCreated', 'DESC')
            ->setMaxResults($max);

        // Execute the query and return the result
        return $queryBuilder->getQuery()->getResult();
    }


    public function findActiveSubscriptionByEngin(Engin $engin)
    {
        $now = new \DateTime();

        return $this->entityManager->getRepository(Subscription::class)->findOneBy([
            'engin' => $engin,
        ]);

    }




}