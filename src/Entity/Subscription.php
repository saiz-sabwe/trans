<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[Groups(['api'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[Groups(['api'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateBegin = null;

    #[Groups(['api'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[Groups(['api'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubscriptionPricing $subscriptionPricing = null;

    #[Groups(['api'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Engin $engin = null;

    #[ORM\Column(nullable: true)]
    private ?int $c2bStatus = null;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(\DateTimeInterface $dateBegin): static
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getSubscriptionPricing(): ?SubscriptionPricing
    {
        return $this->subscriptionPricing;
    }

    public function setSubscriptionPricing(?SubscriptionPricing $subscriptionPricing): static
    {
        $this->subscriptionPricing = $subscriptionPricing;

        return $this;
    }

    public function getEngin(): ?Engin
    {
        return $this->engin;
    }

    public function setEngin(?Engin $engin): static
    {
        $this->engin = $engin;

        return $this;
    }

    public function getC2bStatus(): ?int
    {
        return $this->c2bStatus;
    }

    public function setC2bStatus(?int $c2bStatus): static
    {
        $this->c2bStatus = $c2bStatus;

        return $this;
    }
}
