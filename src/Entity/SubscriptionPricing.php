<?php

namespace App\Entity;

use App\Repository\SubscriptionPricingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubscriptionPricingRepository::class)]
class SubscriptionPricing
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['private'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubscriptionCategory $subscriptionCategoy = null;

    #[ORM\Column]
    private ?int $minDay = null;

    #[ORM\Column]
    private ?int $maxDay = null;

    #[ORM\Column]
    #[Groups(['api'])]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSubscriptionCategoy(): ?SubscriptionCategory
    {
        return $this->subscriptionCategoy;
    }

    public function setSubscriptionCategoy(?SubscriptionCategory $subscriptionCategoy): static
    {
        $this->subscriptionCategoy = $subscriptionCategoy;

        return $this;
    }

    public function getMinDay(): ?int
    {
        return $this->minDay;
    }

    public function setMinDay(int $minDay): static
    {
        $this->minDay = $minDay;

        return $this;
    }

    public function getMaxDay(): ?int
    {
        return $this->maxDay;
    }

    public function setMaxDay(int $maxDay): static
    {
        $this->maxDay = $maxDay;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
