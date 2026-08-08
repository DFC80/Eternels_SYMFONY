<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Entity\ConsumptionItem;
use App\Entity\Equipment;
use App\Entity\Event;
use App\Entity\Game;
use App\Entity\Meal;
use App\Entity\Meeting;
use App\Entity\Membership;
use App\Entity\Photo;
use App\Entity\Subscription;
use App\Entity\SubscriptionRate;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\MembershipRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private MembershipRepository $membershipRepository,
        private SubscriptionRepository $subscriptionRepository,
        private EventRepository $eventRepository,
    ) {}

    public function index(): Response
    {
        $stats = [
            'total_members' => count($this->userRepository->findAll()),
            'active_memberships' => count($this->membershipRepository->findBy(['status' => 'active'])),
            'upcoming_events' => count($this->eventRepository->findUpcomingEvents(100)),
        ];

        return $this->render('admin/dashboard.html.twig', ['stats' => $stats]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Les Eternels - Administration')
            ->setFaviconPath('favicon.ico')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Membres');
        yield MenuItem::linkToCrud('Membres', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Adhesions', 'fas fa-id-card', Membership::class);
        yield MenuItem::linkToCrud('Cotisations', 'fas fa-euro-sign', Subscription::class);
        yield MenuItem::linkToCrud('Tarifs', 'fas fa-tags', SubscriptionRate::class);

        yield MenuItem::section('Activites & Jeux');
        yield MenuItem::linkToCrud('Activites', 'fas fa-gamepad', Activity::class);
        yield MenuItem::linkToCrud('Jeux', 'fas fa-dice', Game::class);

        yield MenuItem::section('Evenements');
        yield MenuItem::linkToCrud('Evenements', 'fas fa-calendar-alt', Event::class);
        yield MenuItem::linkToCrud('Reunions & AG', 'fas fa-people-roof', Meeting::class);
        yield MenuItem::linkToCrud('Repas (Airsoft)', 'fas fa-utensils', Meal::class);

        yield MenuItem::section('Equipements & Consommations');
        yield MenuItem::linkToCrud('Equipements Airsoft', 'fas fa-shield-alt', Equipment::class);
        yield MenuItem::linkToCrud('Articles Bar', 'fas fa-beer', ConsumptionItem::class);

        yield MenuItem::section('Medias');
        yield MenuItem::linkToCrud('Photos', 'fas fa-images', Photo::class);

        yield MenuItem::section('Navigation');
        yield MenuItem::linkToRoute('Retour au site', 'fas fa-arrow-left', 'app_home');
    }
}
