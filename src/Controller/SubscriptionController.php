<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\WalletOperation;
use App\Form\SubscriptionFormType;
use App\Service\ExceptionService;
use App\Service\SubscriptionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubscriptionController extends AbstractController
{

    private SubscriptionService $subscriptionService;
    private ExceptionService $exceptionService;
    private LoggerInterface $logger;

    public function __construct(SubscriptionService $subscriptionService, ExceptionService $exceptionService, LoggerInterface $logger)
    {
        $this->subscriptionService = $subscriptionService;
        $this->exceptionService = $exceptionService;
        $this->logger = $logger;

    }

    #[Route('/subscription', name: 'app_subscription')]
    public function create(Request $request)
    {

        $this->logger->info("# SubscriptionController > create: Start");

        $subscription = new Subscription();

        $form = $this->createForm(SubscriptionFormType::class, $subscription);

        // en cas de soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $this->logger->info("# SubscriptionController > create: form submitted");

                $structure = [
                    'totalDay'=> $form->get('totalDay')->getData(),
                    'registration'=> $form->get('registration')->getData(),
                    'payerAccountNumber'=>$form->get('payerAccountNumber')->getData(),
                    'payerOperator'=>$form->get('payerOperator')->getData(),
                    'payerCurrency'=> $form->get('payerCurrency')->getData()
                ];

                $this->logger->info("# SubscriptionController > create: data submitted", $structure);

                $result = $this->subscriptionService->create($structure);

                $postAction = $result["postAction"];
                $this->logger->info("# SubscriptionController > create: postAction", ["postAction" => $postAction]);


                if (!empty($postAction)) {

                    $this->logger->info("# SubscriptionController > create:post action non nul");

                    $message = $postAction["message"] ?? null;
                    $paymentUrl = $postAction["paymentUrl"] ?? null;

                    if ($paymentUrl !== null) {

                        $this->logger->info("# SubscriptionController > create:pymnt url non nul");

                        return new RedirectResponse($paymentUrl);
                    }
                    $this->logger->info("# SubscriptionController > create: pymnt url nul");// Dans un autre contrôleur ou méthode
                    return $this->redirectToRoute('app_post_action', [
                        'message' => $message,
                        'label' => "success",
                    ]);

                }

                $this->logger->info("# WalletOperationController > create:post action nul");

                return $this->redirectToRoute('app_post_action', [
                    'label' => "danger",
                ]);
            } catch (\Exception $e) {
                $this->logger->info("# SubscriptionController > create:exception ");
                $exception = $this->exceptionService->getException($e);
                $message = $exception['message'];
                $this->logger->info("# SubscriptionController > create :exception", ["message"=>$message]);
                $this->addFlash("danger_message", $exception['message']);
                return $this->redirectToRoute('app_post_action', [
                    'message' => $message,
                    'label' => "danger",
                ]);
            }
        }

        // affiche la vue en cas de non soumission
        return $this->render('subscription/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/subscription/register', name: 'app__subscription_register')]
    public function register(Request $request): Response
    {
        $this->logger->info("# SubscriptionController > register: start");

        $subscriptions = $this->subscriptionService->findLastSubscription(10);
        $processedSubscriptions = [];

        foreach ($subscriptions as $subscription) {
            $subscriptionPricing = $subscription->getSubscriptionPricing();
            $subscriptionCategory = $subscriptionPricing->getSubscriptionCategoy();
            $engin = $subscription->getEngin();

            $processedSubscriptions[] = [
                'id' => $subscription->getId(),
                'amount' => $subscriptionPricing->getAmount(),
                'categoryLabel' => $subscriptionCategory->getLabel(),
                'registration' => $engin->getRegistration(),
                'dateEnd' => $subscription->getDateEnd(),
            ];
        }


        $this->logger->info("# SubscriptionController > register: result", ["lastSubscription" => $processedSubscriptions]);

        return $this->render('subscription/register.html.twig', [
            'subscriptions' => $processedSubscriptions,
        ]);

    }

}
