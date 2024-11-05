<?php

namespace App\Controller\Admin;

use App\Entity\Wallet;
use App\Service\ExceptionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class WalletCrudController extends AbstractCrudController
{
    private UserService $userService;
    private ExceptionService $exceptionService;
    private LoggerInterface $logger;

    public function __construct(UserService $userService, ExceptionService $exceptionService, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->exceptionService = $exceptionService;
        $this->logger = $logger;
    }

    public static function getEntityFqcn(): string
    {
        return Wallet::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->disable(Action::DELETE)
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('owner', 'Propriétaire')->hideOnForm(),
            TextField::new('ownerAccountNumber', 'No.Compte utilisateur')->onlyWhenCreating()->setHelp("Ex: 24381... ou 24399..."),
            NumberField::new("balance", "Solde")->hideOnForm()
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            if (!($entityInstance instanceof Wallet)) {
                return;
            }

            throw new \RuntimeException("Non autorisé", Response::HTTP_UNAUTHORIZED);

//            $ownerAccountNumber = $entityInstance->getOwnerAccountNumber();
//
//            $this->logger->info("# WalletCrudController > persistEntity: Find Owner corresponding to the ownerPhoneNumer given", ['ownerAccountNumber' => $ownerAccountNumber]);
//            $account = $this->userService->findByUsername($ownerAccountNumber);
//            $this->logger->info("# WalletCrudController > persistEntity: Owner corresponding to the ownerPhoneNumer found successfully", ['ownerAccountNumber' => $ownerAccountNumber, 'countWallet' => count($account->getWallets())]);

            //$sameDoc = $currentmerchantProfilDocuments->filter(function($obj) use ($merchantDocumentCategory) {
            //    return $obj->getMerchantDocumentCategory() === $merchantDocumentCategory;
            //})->first();

            //$searchValue = 'Bob';

            //$exists = !empty(array_filter((array)$account->getWallets(), function($obj) use ($searchValue) {
            //    return $obj->name === $searchValue;
            //}));

            //$wallets = $account->getWallets();
            //$walletCategory = $entityInstance->getWalletCategory();

           // $this->logger->info("# WalletCrudController > persistEntity: Before verify if wallet exist", ['ownerAccountNumber' => $ownerAccountNumber, 'walletCategory' => $walletCategory->getApiKey()]);


//            $exists = $wallets->filter(function($obj) use ($account, $walletCategory) {
//                return $obj->getOwner() === $account and $obj->getWalletCategory() == $walletCategory;
//            })->first();
//
//            if ($exists)
//            {
//                $this->logger->info("# WalletCrudController > persistEntity: wallet exist", ['ownerAccountNumber' => $ownerAccountNumber, 'walletCategory' => $walletCategory->getApiKey()]);
//                throw new \RuntimeException("Wallet similaire existe", Response::HTTP_NOT_ACCEPTABLE);
//            }
//
//            $entityInstance->setOwner($account);
//            $entityInstance->setBalance(0.00);
//
//            parent::persistEntity($entityManager, $entityInstance);
//
//            $this->addFlash("success_message", "Compte " . $entityInstance->getWalletCategory()->getLabel() . ": " . $entityInstance->getOwnerAccountNumber() . " enregistré avec succès ");
        } catch (\Exception $e)
        {
            $exception = $this->exceptionService->getException($e);
            $this->addFlash("danger_message", $exception['message']);
        }
    }

}
