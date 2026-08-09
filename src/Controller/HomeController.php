<?php

namespace App\Controller;

use App\Entity\MarketListing;
use App\Form\MarketListingType;
use App\Repository\ActivityRepository;
use App\Repository\EventRepository;
use App\Repository\MarketListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ActivityRepository $activityRepo,
        EventRepository $eventRepo,
        MarketListingRepository $marketRepo
    ): Response {
        $user = $this->getUser();
        $marketForm = null;
        $recentListings = [];

        if ($user) {
            $recentListings = $marketRepo->findRecentActive(3);
            $marketForm = $this->createForm(MarketListingType::class, new MarketListing(), [
                'action' => $this->generateUrl('app_market_new'),
            ]);
        }

        return $this->render('home/index.html.twig', [
            'activities'       => $activityRepo->findBy(['isActive' => true]),
            'upcoming_events'  => $eventRepo->findUpcomingEvents(6),
            'recent_listings'  => $recentListings,
            'market_form'      => $marketForm,
        ]);
    }
}
