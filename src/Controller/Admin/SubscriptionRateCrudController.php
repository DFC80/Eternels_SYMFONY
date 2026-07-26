<?php

namespace App\Controller\Admin;

use App\Entity\SubscriptionRate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubscriptionRateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubscriptionRate::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('activity', 'Activite'),
            TextField::new('label', 'Libelle'),
            MoneyField::new('amount', 'Montant')->setCurrency('EUR'),
            ChoiceField::new('period', 'Periode')
                ->setChoices([
                    'Annuel' => 'annual',
                    'Mensuel' => 'monthly',
                    'Trimestriel' => 'quarterly',
                ]),
            ChoiceField::new('memberCategory', 'Categorie')
                ->setChoices([
                    'Adulte' => 'adult',
                    'Enfant' => 'child',
                    'Etudiant' => 'student',
                    'Famille' => 'family',
                ])
                ->allowEmptyChoices(),
            BooleanField::new('isActive', 'Actif'),
        ];
    }
}
