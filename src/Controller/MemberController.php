<?php

namespace App\Controller;

use App\Entity\EventParticipation;
use App\Repository\ConsumptionRepository;
use App\Repository\EventRepository;
use App\Repository\MembershipRepository;
use App\Repository\SubscriptionRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member')]
#[IsGranted('ROLE_USER')]
class MemberController extends AbstractController
{
    #[Route('/', name: 'app_member_dashboard')]
    public function dashboard(
        MembershipRepository $membershipRepo,
        SubscriptionRepository $subRepo,
        EventRepository $eventRepo,
        ConsumptionRepository $consoRepo
    ): Response {
        $user = $this->getUser();
        return $this->render('member/dashboard.html.twig', [
            'memberships' => $membershipRepo->findBy(['member' => $user]),
            'subscriptions' => $subRepo->findBy(['member' => $user], ['createdAt' => 'DESC'], 5),
            'upcoming_events' => $eventRepo->findUpcomingEvents(5),
            'recent_consumptions' => $consoRepo->findBy(['member' => $user], ['consumedAt' => 'DESC'], 5),
        ]);
    }

    #[Route('/profile', name: 'app_member_profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        return $this->render('member/profile.html.twig', ['user' => $user]);
    }

    #[Route('/events', name: 'app_member_events')]
    public function events(EventRepository $eventRepo): Response
    {
        return $this->render('member/events.html.twig', [
            'events' => $eventRepo->findUpcomingEvents(20),
        ]);
    }

    #[Route('/event/{id}/register', name: 'app_member_event_register', methods: ['POST'])]
    public function registerToEvent(
        int $id,
        EventRepository $eventRepo,
        EntityManagerInterface $em,
        EmailService $emailService
    ): Response {
        $event = $eventRepo->find($id);

        if (!$event || !$event->hasAvailableSpots()) {
            $this->addFlash('error', 'Impossible de s\'inscrire a cet evenement.');
            return $this->redirectToRoute('app_member_events');
        }

        $user = $this->getUser();
        $existing = $em->getRepository(EventParticipation::class)->findOneBy([
            'event' => $event,
            'member' => $user,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous etes deja inscrit a cet evenement.');
            return $this->redirectToRoute('app_member_events');
        }

        $participation = new EventParticipation();
        $participation->setEvent($event);
        $participation->setMember($user);
        $em->persist($participation);
        $em->flush();

        try {
            $emailService->sendEventRegistrationConfirmation($user, $event);
        } catch (\Exception $e) {
            // Log but don't fail if email fails
        }

        $this->addFlash('success', 'Inscription confirmee ! Un email de confirmation vous a ete envoye.');
        return $this->redirectToRoute('app_member_events');
    }

    #[Route('/event/{id}/cancel', name: 'app_member_event_cancel', methods: ['POST'])]
    public function cancelEventRegistration(
        int $id,
        EventRepository $eventRepo,
        EntityManagerInterface $em
    ): Response {
        $event = $eventRepo->find($id);
        $user = $this->getUser();

        if ($event) {
            $existing = $em->getRepository(EventParticipation::class)->findOneBy([
                'event' => $event,
                'member' => $user,
            ]);

            if ($existing) {
                $existing->setStatus('cancelled');
                $em->flush();
                $this->addFlash('success', 'Votre inscription a ete annulee.');
            }
        }

        return $this->redirectToRoute('app_member_events');
    }

    #[Route('/my-events', name: 'app_member_my_events')]
    public function myEvents(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $participations = $em->getRepository(EventParticipation::class)->findBy(
            ['member' => $user],
            ['registeredAt' => 'DESC']
        );

        return $this->render('member/my_events.html.twig', [
            'participations' => $participations,
        ]);
    }
}
