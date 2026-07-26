<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null; // airsoft, board_game, video_game, card_game, etc.

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Membership::class)]
    private Collection $memberships;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: SubscriptionRate::class)]
    private Collection $subscriptionRates;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Photo::class)]
    private Collection $photos;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Game::class)]
    private Collection $games;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
        $this->subscriptionRates = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->games = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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

    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function getSubscriptionRates(): Collection
    {
        return $this->subscriptionRates;
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function isAirsoft(): bool
    {
        return $this->type === 'airsoft';
    }
}
