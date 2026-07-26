<?php

namespace App\Controller\Admin;

use App\Entity\Membership;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class MembershipCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Membership::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('member', 'Membre'),
            AssociationField::new('activity', 'Activite'),
            DateField::new('startDate', 'Date debut'),
            DateField::new('endDate', 'Date fin'),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Actif' => 'active',
                    'Expire' => 'expired',
                    'Annule' => 'cancelled',
                ])
                ->renderAsBadges([
                    'active' => 'success',
                    'expired' => 'warning',
                    'cancelled' => 'danger',
                ]),
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
        ];
    }
}
