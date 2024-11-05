<?php

namespace App\Controller\Admin;

use App\Entity\Agent;
use App\Entity\EnginAgent;
use App\Entity\User;
use App\Repository\AgentRepository;
use App\Repository\EnginRepository;
use App\Service\ExceptionService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EnginAgentCrudController extends AbstractCrudController
{
    private ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    public static function getEntityFqcn(): string
    {
        return EnginAgent::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            //->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->disable(Action::DELETE)
            ->disable(Action::EDIT);
    }


    public function configureFields(string $pageName): iterable
    {
        $connectedUser = $this->getUser();
        $agent = $connectedUser instanceof User ? $connectedUser->getAgent() : null;
        $company = $agent instanceof Agent ? $agent->getCompany() : null;

        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('agent', 'Agent')
                ->setFormTypeOptions([
                    'query_builder' => function (AgentRepository $er) use ($company) {
                        return $company !== null ? $er->findByCompany($company) : null;
                    },
                ]),
            AssociationField::new('engin', 'Engin')
                ->setFormTypeOptions([
                    'query_builder' => function (EnginRepository $er) use ($company) {
                        return $company !== null ? $er->findByCompany($company) : null;
                    },
                ]),
            DateTimeField::new('dateCreated')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            if (!($entityInstance instanceof EnginAgent)) {
                return;
            }

            $entityInstance->setUserCreated($this->getUser());

            parent::persistEntity($entityManager, $entityInstance);
        }
        catch (\Exception $e)
        {
            $exception = $this->exceptionService->getException($e);
            $this->addFlash("danger_message", $exception['message']);
        }
    }
}
