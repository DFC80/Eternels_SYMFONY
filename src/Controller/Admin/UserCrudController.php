<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email', 'Email'),
            TextField::new('firstName', 'Prenom'),
            TextField::new('lastName', 'Nom'),
            TextField::new('phone', 'Telephone')->hideOnIndex(),
            TextField::new('address', 'Adresse')->hideOnIndex(),
            ChoiceField::new('roles', 'Roles')
                ->setChoices(['Admin' => 'ROLE_ADMIN', 'Utilisateur' => 'ROLE_USER'])
                ->allowMultipleChoices()
                ->renderAsBadges(),
            BooleanField::new('isVerified', 'Compte verifie'),
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
        ];
    }
}
