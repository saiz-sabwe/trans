<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

//ALTER TABLE wallet ADD CONSTRAINT constraint_unique_wallet UNIQUE (owner, wallet_category);
#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[UniqueEntity(
    fields: ['owner', 'walletCategory'],
    message: 'Cet utilisateur a un compte similaire',
    errorPath: 'walletCategory'
)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['private'])]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Groups(['api'])]
    private ?float $balance = null;


    //EasyAdmin
    private ?string $ownerAccountNumber = null;

    #[ORM\OneToOne(inversedBy: 'wallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;



    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getOwnerAccountNumber(): ?string
    {
        //return $this->account instanceof User ? $this->getAccount()->getUsername() :  null;
        return $this->ownerAccountNumber;
    }

    public function setOwnerAccountNumber(string $ownerAccountNumber): static
    {
        $this->ownerAccountNumber = $ownerAccountNumber;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
