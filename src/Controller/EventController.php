<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_events')]
    public function index(EventRepository $eventRepo): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepo->findUpcomingEvents(20),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EventRepository $eventRepo): Response
    {
        $event = $eventRepo->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Evenement non trouve');
        }

        return $this->render('event/show.html.twig', ['event' => $event]);
    }
}
