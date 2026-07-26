<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ActivityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Airsoft' => 'airsoft',
                    'Jeu de plateau' => 'board_game',
                    'Jeu video' => 'video_game',
                    'Jeu de cartes' => 'card_game',
                    'Autre' => 'other',
                ]),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            BooleanField::new('isActive', 'Active'),
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
        ];
    }
}
