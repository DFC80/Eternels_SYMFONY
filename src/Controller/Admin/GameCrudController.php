<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GameCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Game::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            AssociationField::new('activity', 'Activite'),
            TextField::new('publisher', 'Editeur')->hideOnIndex(),
            IntegerField::new('year', 'Annee')->hideOnIndex(),
            IntegerField::new('minPlayers', 'Joueurs min'),
            IntegerField::new('maxPlayers', 'Joueurs max'),
            ChoiceField::new('difficulty', 'Difficulte')
                ->setChoices([
                    'Facile' => 'easy',
                    'Moyen' => 'medium',
                    'Difficile' => 'hard',
                    'Expert' => 'expert',
                ])
                ->allowEmptyChoices(),
            IntegerField::new('averageDuration', 'Duree moy. (min)')->hideOnIndex(),
            BooleanField::new('isAvailable', 'Disponible'),
            TextareaField::new('description', 'Description')->hideOnIndex(),
        ];
    }
}
