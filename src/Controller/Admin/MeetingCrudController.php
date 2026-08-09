<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Meeting;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MeetingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Meeting::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Réunion de bureau' => Meeting::TYPE_BUREAU,
                    'Assemblée Générale' => Meeting::TYPE_ASSEMBLEE_GENERALE,
                ])
                ->renderAsBadges([
                    Meeting::TYPE_BUREAU => 'secondary',
                    Meeting::TYPE_ASSEMBLEE_GENERALE => 'warning',
                ]),
            DateTimeField::new('date', 'Date'),
            TextField::new('location', 'Lieu')->setRequired(false),
            TextareaField::new('agenda', 'Ordre du jour')->hideOnIndex()->setRequired(false),
            TextareaField::new('notes', 'Compte-rendu')->hideOnIndex()->setRequired(false),
            BooleanField::new('isPublished', 'Affiché sur l\'accueil')
                ->setHelp('Pour les Assemblées Générales uniquement'),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Meeting $meeting */
        $meeting = $entityInstance;
        $meeting->setUpdatedAt(new \DateTimeImmutable());

        if ($meeting->getDate() !== null) {
            $this->syncLinkedEvent($entityManager, $meeting);
        }

        parent::persistEntity($entityManager, $meeting);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Meeting $meeting */
        $meeting = $entityInstance;
        $meeting->setUpdatedAt(new \DateTimeImmutable());

        if ($meeting->getDate() !== null) {
            $this->syncLinkedEvent($entityManager, $meeting);
        }

        parent::updateEntity($entityManager, $meeting);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Meeting $meeting */
        $meeting = $entityInstance;

        $linkedEvent = $meeting->getLinkedEvent();
        if ($linkedEvent !== null) {
            $meeting->setLinkedEvent(null);
            $entityManager->remove($linkedEvent);
        }

        parent::deleteEntity($entityManager, $meeting);
    }

    private function syncLinkedEvent(EntityManagerInterface $em, Meeting $meeting): void
    {
        $date = $meeting->getDate();
        $year = $date->format('Y');

        if ($meeting->getType() === Meeting::TYPE_BUREAU) {
            $title = sprintf('Réunion de bureau — %s', $date->format('d/m/Y'));
            $description = $meeting->getAgenda() ?? 'Réunion interne du bureau de l\'association.';
            $bureauOnly = true;
        } else {
            $title = sprintf('Assemblée Générale %s', $year);
            $description = $meeting->getAgenda() ?? sprintf('Assemblée générale annuelle %s de l\'association Les Éternels.', $year);
            $bureauOnly = false;
        }

        $endDate = (clone $date)->modify('+3 hours');

        $event = $meeting->getLinkedEvent();
        if ($event === null) {
            $event = new Event();
            $event->setCreatedAt(new \DateTimeImmutable());
        }

        $event->setTitle($title);
        $event->setDescription($description);
        $event->setStartDate($date);
        $event->setEndDate($endDate);
        $event->setLocation($meeting->getLocation());
        $event->setStatus('open');
        $event->setBureauOnly($bureauOnly);

        $em->persist($event);
        $meeting->setLinkedEvent($event);
    }
}
