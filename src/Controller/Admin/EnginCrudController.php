<?php

namespace App\Controller\Admin;

use App\Entity\Engin;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EnginCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Engin::class;
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
            AssociationField::new('company', 'Société'),
            AssociationField::new('owner', 'Propriétaire')->hideOnForm(),
            AssociationField::new('enginCategory', 'Catégorie'),
            TextField::new('label', 'Dénomination'),
            TextField::new('chassis', 'Chassis'),
            TextField::new('registration', 'Immatriculation'),
            IntegerField::new('seat', 'Nbre Place'),
            DateTimeField::new('dateCreated')->hideOnForm(),
            BooleanField::new('isWorking', "Est actif")->hideWhenCreating()
        ];
    }
}
