<?php

namespace App\Entity;

use App\Repository\WalletOperationItineraryDetailsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WalletOperationItineraryDetailsRepository::class)]
class WalletOperationItineraryDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'walletOperationItineraryDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WalletOperation $walletOperation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ItineraryPricing $itineraryPricing = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getWalletOperation(): ?WalletOperation
    {
        return $this->walletOperation;
    }

    public function setWalletOperation(?WalletOperation $walletOperation): static
    {
        $this->walletOperation = $walletOperation;

        return $this;
    }

    public function getItineraryPricing(): ?ItineraryPricing
    {
        return $this->itineraryPricing;
    }

    public function setItineraryPricing(?ItineraryPricing $itineraryPricing): static
    {
        $this->itineraryPricing = $itineraryPricing;

        return $this;
    }
}
