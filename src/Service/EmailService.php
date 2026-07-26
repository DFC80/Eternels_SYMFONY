<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Membership;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private string $fromEmail = 'noreply@les-eternels.fr',
        private string $associationName = 'Les Eternels'
    ) {}

    public function sendAccountVerification(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Confirmez votre compte - ' . $this->associationName)
            ->htmlTemplate('emails/account_verification.html.twig')
            ->context([
                'user' => $user,
                'verification_url' => $this->urlGenerator->generate(
                    'app_verify_email',
                    ['token' => $user->getVerificationToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'association_name' => $this->associationName,
            ]);

        $this->mailer->send($email);
    }

    public function sendMembershipConfirmation(User $user, Membership $membership): void
    {
        $email = (new TemplatedEmail())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Adhesion confirmee - ' . $this->associationName)
            ->htmlTemplate('emails/membership_confirmation.html.twig')
            ->context([
                'user' => $user,
                'membership' => $membership,
                'association_name' => $this->associationName,
            ]);

        $this->mailer->send($email);
    }

    public function sendEventRegistrationConfirmation(User $user, Event $event): void
    {
        $email = (new TemplatedEmail())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Inscription a ' . $event->getTitle() . ' confirmee')
            ->htmlTemplate('emails/event_registration.html.twig')
            ->context([
                'user' => $user,
                'event' => $event,
                'association_name' => $this->associationName,
            ]);

        $this->mailer->send($email);
    }
}
