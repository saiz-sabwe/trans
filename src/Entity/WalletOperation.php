<?php

namespace App\Entity;

use App\Repository\WalletOperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WalletOperationRepository::class)]
class WalletOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Wallet $wallet = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 30)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?int $closedStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateClosed = null;

    #[ORM\OneToOne(mappedBy: 'walletOperation', cascade: ['persist', 'remove'])]
    private ?WalletOperationDetail $walletOperationDetail = null;

    /**
     * @var Collection<int, WalletOperationItineraryDetails>
     */
    #[ORM\OneToMany(targetEntity: WalletOperationItineraryDetails::class, mappedBy: 'walletOperation')]
    private Collection $walletOperationItineraryDetails;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subscription $subsciption = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $channel = null;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->walletOperationItineraryDetails = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): static
    {
        $this->wallet = $wallet;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getClosedStatus(): ?int
    {
        return $this->closedStatus;
    }

    public function setClosedStatus(?int $closedStatus): static
    {
        $this->closedStatus = $closedStatus;

        return $this;
    }

    public function getDateClosed(): ?\DateTimeInterface
    {
        return $this->dateClosed;
    }

    public function setDateClosed(?\DateTimeInterface $dateClosed): static
    {
        $this->dateClosed = $dateClosed;

        return $this;
    }

    public function getWalletOperationDetail(): ?WalletOperationDetail
    {
        return $this->walletOperationDetail;
    }

    public function setWalletOperationDetail(WalletOperationDetail $walletOperationDetail): static
    {
        // set the owning side of the relation if necessary
        if ($walletOperationDetail->getWalletOperation() !== $this) {
            $walletOperationDetail->setWalletOperation($this);
        }

        $this->walletOperationDetail = $walletOperationDetail;

        return $this;
    }

    /**
     * @return Collection<int, WalletOperationItineraryDetails>
     */
    public function getWalletOperationItineraryDetails(): Collection
    {
        return $this->walletOperationItineraryDetails;
    }

    public function addWalletOperationItineraryDetail(WalletOperationItineraryDetails $walletOperationItineraryDetail): static
    {
        if (!$this->walletOperationItineraryDetails->contains($walletOperationItineraryDetail)) {
            $this->walletOperationItineraryDetails->add($walletOperationItineraryDetail);
            $walletOperationItineraryDetail->setWalletOperation($this);
        }

        return $this;
    }

    public function removeWalletOperationItineraryDetail(WalletOperationItineraryDetails $walletOperationItineraryDetail): static
    {
        if ($this->walletOperationItineraryDetails->removeElement($walletOperationItineraryDetail)) {
            // set the owning side to null (unless already changed)
            if ($walletOperationItineraryDetail->getWalletOperation() === $this) {
                $walletOperationItineraryDetail->setWalletOperation(null);
            }
        }

        return $this;
    }

    public function getSubsciption(): ?Subscription
    {
        return $this->subsciption;
    }

    public function setSubsciption(?Subscription $subsciption): static
    {
        $this->subsciption = $subsciption;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }
}
