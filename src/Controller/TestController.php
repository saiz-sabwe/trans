<?php

namespace App\Controller;

use App\Entity\SubscriptionCategory;
use App\Entity\SubscriptionPricing;
use App\Service\ExceptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{


    private ExceptionService $exceptionService;

    private LoggerInterface $logger;

    private EntityManagerInterface $entityManager;

    public function __construct(ExceptionService $exceptionService,LoggerInterface $logger,EntityManagerInterface $entityManager)
    {

        $this->exceptionService = $exceptionService;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

    }

    #[Route('/detail/{label}/{apiKey}', name: 'app_detail')]
    public function insertSubscriptionDetail(string $label, string $apiKey): JsonResponse
    {
        // Créer une nouvelle catégorie d'abonnement avec les valeurs dynamiques
        $subscriptionCategory = new SubscriptionCategory();
        $subscriptionCategory->setLabel($label);
        $subscriptionCategory->setApiKey($apiKey);

        // Persister dans la base de données
        $this->entityManager->persist($subscriptionCategory);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Subscription category created successfully!',
            'label' => $label,
            'apiKey' => $apiKey,
        ]);
    }

    #[Route('/pricing/{label}/{minDay}/{maxDay}/{amount}', name: 'app_pricing')]
    public function insertSubscriptionPricing(string $label, int $minDay, int $maxDay, float $amount): JsonResponse
    {
        // Rechercher une SubscriptionCategory avec le label passé en paramètre
        $subscriptionCategory = $this->entityManager->getRepository(SubscriptionCategory::class)->findOneBy(['label' => $label]);

        if (!$subscriptionCategory) {
            return $this->json([
                'message' => 'Subscription Category not found',
            ], 404);
        }

        // Créer une nouvelle instance de SubscriptionPricing
        $subscriptionPricing = new SubscriptionPricing();
        $subscriptionPricing->setSubscriptionCategoy($subscriptionCategory);
        $subscriptionPricing->setMinDay($minDay);
        $subscriptionPricing->setMaxDay($maxDay);
        $subscriptionPricing->setAmount($amount);
        $subscriptionPricing->setDateCreated(new \DateTime());

        // Persister les données
        $this->entityManager->persist($subscriptionPricing);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Subscription Pricing created successfully!',
            'pricingId' => $subscriptionPricing->getId(),
        ]);
    }


  #[Route('/send', name: 'app_send')]
    public function send(): JsonResponse
    {
        $httpClient = HttpClient::create();

        $this->logger->info("# TestController > send : Start");

        $url = 'https://api.onesignal.com/notifications';
        $headers = [
            'Authorization' => 'Basic os_v2_app_xrfcceombbcwfdezh7mgr3ss4ebbxqxz6c5elp4aek6gu7f3yt6qak2fu33vmbjbsrxq44tvnmhq5wrs5cja5av7oy42m65k5ldsaly',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
//        $data = [
//            'app_id' => 'bc4a2111-cc08-4562-8c99-3fd868ee52e1',
//            'target_channel' => 'push',
//            'contents' => [
//                'en' => 'English Message',
//            ],
//            'included_segments' => ['All'],
//        ];
        $data = [
            'app_id' => 'bc4a2111-cc08-4562-8c99-3fd868ee52e1',
            'target_channel' => 'push',
            'headings' => [
                'en' => 'Makuta Trans',
            ],
            'contents' => [
                'en' => 'English Message',
            ],
            'include_aliases' => [
                'external_id' => [
                    '200',
                ],
            ],
            'data'=> [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ];

        try {
            $response = $httpClient->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            // Parse the response
            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            return $this->json([
                'status' => $statusCode,
                'response' => $content,
            ]);
        } catch (\Exception $e) {

            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }


//        $httpClient = HttpClient::create();
//
//        $url = 'https://api.onesignal.com/notifications';
//
//        $headers = [
//            'Authorization' => 'Basic os_v2_app_k63tg5mrjjcopctdymxwjuzodfynjp3bsa2umjuromtngogix2nc2vpmelpla7q3hzbkpqq5r54e6ws6vamftmbiidwgsh3grwzjz2i',
//            'Accept' => 'application/json',
//            'Content-Type' => 'application/json',
//        ];
//        $data = [
//            'app_id' => '57b73375-914a-44e7-8a63-c32f64d32e19',
//            'target_channel' => 'push',
//            'contents' => [
//                'fr' => 'English Message'
//            ],
//            'included_segments' => ['Subscribed Users'],
//        ];
//
//        try {
//            $response = $httpClient->request('POST', $url, [
//                'headers' => $headers,
//                'json' => $data,
//            ]);
//
//
//
//            $statusCode = $response->getStatusCode(); // 200, 400, etc.
//            $content = $response->toArray(); // Convert JSON response to an array
//
//            $this->logger->info("# TestController > send : content", ["content"=>$content]);
//
//            return $this->json([
//                'status' => $statusCode,
//                'response' => $content,
//            ]);
//
//        } catch (\Exception $e) {
//            return $this->json([
//                'status' => 'error',
//                'message' => $e->getMessage(),
//            ]);
//
//        }





}
