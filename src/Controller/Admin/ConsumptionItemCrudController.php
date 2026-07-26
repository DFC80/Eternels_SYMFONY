<?php

namespace App\Controller\Admin;

use App\Entity\ConsumptionItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConsumptionItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsumptionItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            ChoiceField::new('category', 'Categorie')
                ->setChoices([
                    'Boisson' => 'drink',
                    'Nourriture' => 'food',
                    'En-cas' => 'snack',
                ]),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            BooleanField::new('isAvailable', 'Disponible'),
        ];
    }
}
