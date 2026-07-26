<?php

namespace App\Controller\Admin;

use App\Entity\Subscription;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class SubscriptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subscription::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('member', 'Membre'),
            AssociationField::new('rate', 'Tarif'),
            MoneyField::new('amountPaid', 'Montant paye')->setCurrency('EUR'),
            DateField::new('paymentDate', 'Date paiement'),
            DateField::new('validFrom', 'Valide du'),
            DateField::new('validUntil', 'Valide jusqu au'),
            ChoiceField::new('paymentMethod', 'Mode paiement')
                ->setChoices([
                    'Especes' => 'cash',
                    'Carte' => 'card',
                    'Virement' => 'transfer',
                    'Cheque' => 'check',
                ]),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Paye' => 'paid',
                    'En attente' => 'pending',
                    'Annule' => 'cancelled',
                ])
                ->renderAsBadges([
                    'paid' => 'success',
                    'pending' => 'warning',
                    'cancelled' => 'danger',
                ]),
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
        ];
    }
}
