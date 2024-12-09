<?php

namespace App\Controller\Admin;

use App\Entity\AgentParking;
use App\Repository\AgentRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AgentParkingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AgentParking::class;
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
            AssociationField::new('agent', "Agent")
                ->setFormTypeOptions([
                    'query_builder' => function (AgentRepository $er) {
                        return $er->findByRole(["VERIFICATOR", "COLLECTOR"]);
                    },
                ])->setFormTypeOption('placeholder', 'Sélectionnez un agent'),
            AssociationField::new('parking', 'Parking')->setFormTypeOption('placeholder', 'Sélectionnez un Parking'),
            BooleanField::new('isDeleted', "Est supprimé")->hideWhenCreating(),
            DateTimeField::new('createdAt', 'Date de création')->hideOnForm(),
        ];
    }

}
