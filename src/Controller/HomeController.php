<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActivityRepository $activityRepo, EventRepository $eventRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'activities' => $activityRepo->findBy(['isActive' => true]),
            'upcoming_events' => $eventRepo->findUpcomingEvents(6),
        ]);
    }
}
