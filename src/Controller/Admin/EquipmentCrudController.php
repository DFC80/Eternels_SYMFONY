<?php

namespace App\Controller\Admin;

use App\Entity\Equipment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EquipmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Equipment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            ChoiceField::new('category', 'Categorie')
                ->setChoices([
                    'Arme' => 'weapon',
                    'Protection' => 'protection',
                    'Vetement' => 'clothing',
                    'Accessoire' => 'accessory',
                    'Vehicule' => 'vehicle',
                ]),
            IntegerField::new('quantity', 'Quantite'),
            ChoiceField::new('condition', 'Etat')
                ->setChoices([
                    'Neuf' => 'new',
                    'Bon' => 'good',
                    'Correct' => 'fair',
                    'Mauvais' => 'poor',
                    'Hors service' => 'out_of_service',
                ])
                ->renderAsBadges([
                    'new' => 'success',
                    'good' => 'info',
                    'fair' => 'warning',
                    'poor' => 'danger',
                    'out_of_service' => 'dark',
                ]),
            MoneyField::new('purchasePrice', 'Prix achat')->setCurrency('EUR')->setRequired(false)->hideOnIndex(),
            DateField::new('purchaseDate', 'Date achat')->hideOnIndex(),
            TextField::new('serialNumber', 'Numero serie')->hideOnIndex(),
            AssociationField::new('assignedTo', 'Assigne a')->setRequired(false),
            BooleanField::new('isAvailable', 'Disponible'),
            TextareaField::new('description', 'Description')->hideOnIndex(),
        ];
    }
}
