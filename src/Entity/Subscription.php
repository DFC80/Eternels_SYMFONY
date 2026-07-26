<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $member = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubscriptionRate $rate = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $amountPaid = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $validFrom = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $validUntil = null;

    #[ORM\Column(length: 50)]
    private ?string $paymentMethod = 'cash'; // cash, card, transfer, check

    #[ORM\Column(length: 30)]
    private ?string $status = 'paid'; // paid, pending, cancelled

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): static
    {
        $this->member = $member;
        return $this;
    }

    public function getRate(): ?SubscriptionRate
    {
        return $this->rate;
    }

    public function setRate(?SubscriptionRate $rate): static
    {
        $this->rate = $rate;
        return $this;
    }

    public function getAmountPaid(): ?string
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(string $amountPaid): static
    {
        $this->amountPaid = $amountPaid;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getValidFrom(): ?\DateTimeInterface
    {
        return $this->validFrom;
    }

    public function setValidFrom(\DateTimeInterface $validFrom): static
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeInterface $validUntil): static
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
