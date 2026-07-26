<?php

namespace App\Controller;

use App\Entity\EventParticipation;
use App\Entity\Membership;
use App\Form\ProfileFormType;
use App\Repository\ActivityRepository;
use App\Repository\ConsumptionRepository;
use App\Repository\EventRepository;
use App\Repository\MembershipRepository;
use App\Repository\SubscriptionRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/profile', name: 'app_member_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_member_profile');
        }

        return $this->render('member/profile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile/password', name: 'app_member_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('change_password', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_member_profile');
        }

        $current  = $request->request->get('current_password', '');
        $new      = $request->request->get('new_password', '');
        $confirm  = $request->request->get('confirm_password', '');

        if (!$hasher->isPasswordValid($user, $current)) {
            $this->addFlash('error', 'Mot de passe actuel incorrect.');
            return $this->redirectToRoute('app_member_profile');
        }

        if (strlen($new) < 8) {
            $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_member_profile');
        }

        if ($new !== $confirm) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_member_profile');
        }

        $user->setPassword($hasher->hashPassword($user, $new));
        $user->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Mot de passe modifié avec succès.');
        return $this->redirectToRoute('app_member_profile');
    }

    #[Route('/memberships', name: 'app_member_memberships')]
    public function memberships(
        MembershipRepository $membershipRepo,
        ActivityRepository $activityRepo
    ): Response {
        $user = $this->getUser();

        return $this->render('member/memberships.html.twig', [
            'memberships'        => $membershipRepo->findByMember($user),
            'active_activity_ids' => $membershipRepo->findActiveActivityIds($user),
            'activities'         => $activityRepo->findBy(['isActive' => true], ['name' => 'ASC']),
        ]);
    }

    #[Route('/memberships/join/{activityId}', name: 'app_member_membership_join', methods: ['POST'])]
    public function joinMembership(
        int $activityId,
        ActivityRepository $activityRepo,
        MembershipRepository $membershipRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $activity = $activityRepo->find($activityId);

        if (!$activity) {
            $this->addFlash('error', 'Activité introuvable.');
            return $this->redirectToRoute('app_member_memberships');
        }

        $existing = $membershipRepo->findOneBy(['member' => $user, 'activity' => $activity, 'status' => 'active']);
        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà membre de cette activité.');
            return $this->redirectToRoute('app_member_memberships');
        }

        $membership = new Membership();
        $membership->setMember($user);
        $membership->setActivity($activity);
        $membership->setStartDate(new \DateTime());
        $membership->setEndDate((new \DateTime())->modify('+1 year'));
        $membership->setStatus('active');
        $em->persist($membership);
        $em->flush();

        $this->addFlash('success', 'Vous avez rejoint l\'activité ' . $activity->getName() . ' !');
        return $this->redirectToRoute('app_member_memberships');
    }

    #[Route('/memberships/{id}/cancel', name: 'app_member_membership_cancel', methods: ['POST'])]
    public function cancelMembership(
        int $id,
        MembershipRepository $membershipRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $membership = $membershipRepo->find($id);

        if (!$membership || $membership->getMember() !== $user) {
            $this->addFlash('error', 'Adhésion introuvable.');
            return $this->redirectToRoute('app_member_memberships');
        }

        $membership->setStatus('cancelled');
        $em->flush();

        $this->addFlash('success', 'Adhésion annulée.');
        return $this->redirectToRoute('app_member_memberships');
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
