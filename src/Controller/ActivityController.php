<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
class ActivityController extends AbstractController
{
    #[Route('/', name: 'app_activities')]
    public function index(ActivityRepository $activityRepo): Response
    {
        return $this->render('activity/index.html.twig', [
            'activities' => $activityRepo->findBy(['isActive' => true]),
        ]);
    }

    #[Route('/{id}', name: 'app_activity_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ActivityRepository $activityRepo): Response
    {
        $activity = $activityRepo->find($id);
        if (!$activity) {
            throw $this->createNotFoundException('Activite non trouvee');
        }

        return $this->render('activity/show.html.twig', ['activity' => $activity]);
    }
}
