<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\WalletOperationService;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BaseController extends AbstractController
{

    private UserService $userService;
    private WalletOperationService $walletOperationService;
    private WalletService $walletService;

    public function __construct(UserService $userService, WalletOperationService $walletOperationService, WalletService $walletService)
    {
        $this->userService = $userService;
        $this->walletService = $walletService;
        $this->walletOperationService = $walletOperationService;
    }


    #[Route('/', name: 'app_base_home')]
    public function index(): Response
    {
        $user = $this->getUser();

        $balance = $this->walletService->getBalance($user);

        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
            'balance'=> $balance
        ]);
    }

    #[Route('/user/info', name: 'app_base_user_info')]
    public function showUser(): Response
    {

        $user = $this->userService->getCurrentUSer();

        return $this->render('base/user_info.html.twig', [
            'user' => $user,
            'slogan' => "Profitez d'un voyage en taxi alliant confort, sécurité et tranquillité. Nos véhicules sont soigneusement entretenus pour offrir une expérience agréable, tandis que nos chauffeurs professionnels s'assurent de vous conduire à destination avec ponctualité et courtoisie. Voyagez l’esprit léger !"
        ]);
    }

    #[Route('/wallet-operation/activity', name: 'app_wallet_operation_activity')]
    public function getLastOperation(): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User))
        {
            throw new \RuntimeException("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
        }

        $lastOperations = $this->walletOperationService->getLatestOperation();
       

        return $this->render('base/wallet.html.twig', [
            'operations' => $lastOperations
        ]);
    }
}
