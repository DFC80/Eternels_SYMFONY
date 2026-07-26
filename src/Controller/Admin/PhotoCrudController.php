<?php

namespace App\Controller\Admin;

use App\Entity\Photo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('activity', 'Activite'),
            AssociationField::new('event', 'Evenement')->setRequired(false),
            TextField::new('filename', 'Fichier'),
            TextField::new('caption', 'Legende'),
            AssociationField::new('uploadedBy', 'Uploade par'),
            BooleanField::new('isPublic', 'Public'),
            DateTimeField::new('uploadedAt', 'Date upload')->hideOnForm(),
        ];
    }
}
