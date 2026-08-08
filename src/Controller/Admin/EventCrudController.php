<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            AssociationField::new('activity', 'Activite'),
            DateTimeField::new('startDate', 'Date debut'),
            DateTimeField::new('endDate', 'Date fin'),
            TextField::new('location', 'Lieu'),
            IntegerField::new('maxParticipants', 'Max participants'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR')->setRequired(false),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Planifie' => 'planned',
                    'Ouvert' => 'open',
                    'En cours' => 'ongoing',
                    'Termine' => 'completed',
                    'Annule' => 'cancelled',
                ])
                ->renderAsBadges([
                    'planned' => 'secondary',
                    'open' => 'success',
                    'ongoing' => 'primary',
                    'completed' => 'info',
                    'cancelled' => 'danger',
                ]),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            BooleanField::new('bureauOnly', 'Bureau uniquement')
                ->setHelp('Si coché, cet événement n\'est visible que par les membres du bureau'),
        ];
    }
}
