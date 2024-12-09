<?php

namespace App\Controller\Admin;

use App\Entity\Agent;
use App\Service\ExceptionService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;

class AgentCrudController extends AbstractCrudController
{
    private ExceptionService $exceptionService;
    private LoggerInterface $logger;
    private UserService $userService;

    public function __construct(ExceptionService $exceptionService, LoggerInterface $logger, UserService $userService)
    {
        $this->exceptionService = $exceptionService;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    public static function getEntityFqcn(): string
    {
        return Agent::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->disable(Action::DELETE);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('company', 'Entité')->setSortProperty('label')
            ->setHelp("Sélectionnez la dénomination d'une entité Ex: Société ou Entité Gouvernementale")
                ->setFormTypeOption('placeholder', 'Sélectionnez une entité'),
            TextField::new('userAccountNumber', 'No.Compte utilisateur')->onlyOnForms()->setHelp("Ex: 24381... ou 24399...")->setFormTypeOption('required', true),
            AssociationField::new('account', 'No.Compte utilisateur')->onlyOnDetail(),
            TextField::new('matricule', 'No. Matricule'),
            TextField::new('fullname', 'Nom complet'),
            TextField::new('birthPlace', 'Lieu de naissance'),
            DateField::new('birthDate', 'Date de naissance'),
            TelephoneField::new('phone', 'Téléphone'),
            EmailField::new('email', 'E-mail')->hideOnIndex(),
            TextEditorField::new('address', 'Adresse physique')->hideOnIndex(),
            BooleanField::new('isDeleted', "Est supprimé")->hideWhenCreating()
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            if (!($entityInstance instanceof Agent)) {
                return;
            }

            $account = $this->userService->findByUsername($entityInstance->getUserAccountNumber());

            $entityInstance->setAccount($account);

            parent::persistEntity($entityManager, $entityInstance);

            $this->addFlash("success_message", "Enregistrement de " . strtoupper($entityInstance->getFullname()) . " réussi");
        } catch (\Exception $e)
        {
            $exception = $this->exceptionService->getException($e);
            $this->addFlash("danger_message", $exception['message']);
        }
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            if (!($entityInstance instanceof Agent)) {
                return;
            }

            $account = $this->userService->findByUsername($entityInstance->getUserAccountNumber());

            $entityInstance->setAccount($account);

            parent::persistEntity($entityManager, $entityInstance);

            $this->addFlash("success_message", "Modification de " . strtoupper($entityInstance->getFullname()) . " réussie");
        } catch (\Exception $e)
        {
            $exception = $this->exceptionService->getException($e);
            $this->addFlash("danger_message", $exception['message']);
        }
    }
}
