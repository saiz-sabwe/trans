<?php

namespace App\Controller;

use App\Entity\WalletOperation;
use App\Entity\WalletOperationDetail;
use App\Form\WalletOperationType;
use App\Service\ExceptionService;
use App\Service\WalletOperationService;
use App\Service\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WalletOperationController extends AbstractController
{


    private WalletOperationService $walletOperationService;
    private LoggerInterface $logger;
    private ExceptionService $exceptionService;

    public function __construct(WalletOperationService $walletOperationService,  LoggerInterface $logger, ExceptionService $exceptionService)
    {
        $this->walletOperationService = $walletOperationService;
        $this->logger = $logger;
        $this->exceptionService = $exceptionService;
    }

    #[Route('/wallet/operation', name: 'app_wallet_operation')]
    public function createWalletOperation(Request $request)
    {

        $this->logger->info("# WalletOperationController > createWalletOperation: Start");

        $walletOperation = new WalletOperation();

        $form = $this->createForm(WalletOperationType::class, $walletOperation);

        // en cas de soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $this->logger->info("# WalletOperationController > createWalletOperation: form submitted");// Récupérer les valeurs du formulaire

                $payerOperator = $form->get('payerOperator')->getData();
                $payerCurrency = $form->get('payerCurrency')->getData();
                $payerAccountNumber = $form->get('payerAccountNumber')->getData();
                $amount = $form->get('amount')->getData();// Préparer un tableau associatif avec les valeurs

                $formData = [
                    'payerOperator' => $payerOperator,
                    'payerCurrency' => $payerCurrency,
                    'payerAccountNumber' => $payerAccountNumber,
                    'amount' => $amount,
                ];// Loguer les données du formulaire

                $this->logger->info("# WalletOperationController > createWalletOperation: data submitted", $formData);

                $result = $this->walletOperationService->createTopup($amount, $payerOperator, $payerCurrency, $payerAccountNumber);

//                $result = [
//                    "postAction"=>[
//                        "message"=>"Votre message ya tokosssssssss < recharge",
//                    ]
//                ];

                $postAction = $result["postAction"];
                $this->logger->info("# WalletOperationController > createWalletOperation: postAction", ["postAction" => $postAction]);


                if (!empty($postAction)) {

                    $this->logger->info("# WalletOperationController > createWalletOperation:post action non nul");

                    $message = $postAction["message"] ?? null;
                    $paymentUrl = $postAction["paymentUrl"] ?? null;

                    if ($paymentUrl !== null) {

                        $this->logger->info("# WalletOperationController > createWalletOperation:pymnt url non nul");

                        return new RedirectResponse($paymentUrl);
                    }
                    $this->logger->info("# WalletOperationController > createWalletOperation: pymnt url nul");// Dans un autre contrôleur ou méthode
                    $this->addFlash('success_message', $message);
                    return $this->redirectToRoute('app_base_home');

                }

                $this->logger->info("# WalletOperationController > createWalletOperation:post action nul");

                return $this->redirectToRoute('app_base_home');

            } catch (\Exception $e) {
                $this->logger->info("# WalletOperationController > createWalletOperation:exception ");
                $exception = $this->exceptionService->getException($e);
                $message = $exception['message'];
                $this->logger->info("# WalletOperationController > createWalletOperation:exception", ["message"=>$message]);
                $this->addFlash("danger_message", $exception['message']);
                return $this->render('wallet_operation/index.html.twig', [
                    'form' => $form->createView(),
                ]);


            }
        }

        // affiche la vue en cas de non soumission
        return $this->render('wallet_operation/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

//    #[Route('/wallet/operation/result', name: 'app_post_action')]
//    public function index(): Response
//    {
//        return $this->render('wallet_operation/post_action.html.twig');
//        return $this->render('wallet_operation/post_action.html.twig', [
//            'message' => $message,
//            'label' => $label,
//        ]);
//    }


    #[Route('/makuta/callback', name: 'app_makuta_callback', methods: ['POST'])]
    public function handleMakutaCallback(Request $request)
    {
        try
        {
            $data = $request->toArray();


        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
        }

        // Récupérer le contenu du callback
        $callback = json_decode($request->getContent(), true);

        // Traiter les données du callback
        if ($callback) {

            $financialTransaction = $callback['contents']['financialTransaction'];

            $code = (int) $callback['code'];
            $message = $callback['message'];
            $isC2bSuccess = $callback['contents']['isC2bSuccess'];

            if ($code === Response::HTTP_OK && $isC2bSuccess === true) {

                $this->walletOperationService->closeTopup($financialTransaction,true);

            }else{

                $this->walletOperationService->closeTopup($financialTransaction,false);

            }

            return new JsonResponse([
                'message' => $message,
                'financialTransaction' => $financialTransaction
            ], $code);


        }

        return new JsonResponse(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);

    }

//    #[Route('/makuta/redirect', name: 'app_makuta_redirect')]
    #[Route('/drc/card/redirect-url', name: 'app_makuta_redirect')]
    public function redirectVisa(): Response
    {
        // Dans un autre contrôleur ou méthode
        $this->addFlash('success_message', "paiement en cours");
        return $this->redirectToRoute('app_base_home');

    }

}
