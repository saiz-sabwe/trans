<?php

namespace App\Entity;

use App\Repository\EnginRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EnginRepository::class)]
class Engin
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['private'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $chassis = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['api'])]
    private ?string $registration = null;

    #[ORM\Column(nullable: true)]
    private ?int $seat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isWorking = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?EnginCategory $enginCategory = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $owner = null;

    #[Groups(['api'])]
    private ?string $ownerPseudo = null;

    #[Groups(['api'])]
    private ?string $ownerPhoneNumber = null;

    #[Groups(['secret'])]
    private ?string $registrationCipher = null;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->isWorking = true;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getChassis(): ?string
    {
        return $this->chassis;
    }

    public function setChassis(string $chassis): static
    {
        $this->chassis = $chassis;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): static
    {
        $this->registration = $registration;

        return $this;
    }

    public function getSeat(): ?int
    {
        return $this->seat;
    }

    public function setSeat(int $seat): static
    {
        $this->seat = $seat;

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

    public function isWorking(): ?bool
    {
        return $this->isWorking;
    }

    public function setWorking(bool $isWorking): static
    {
        $this->isWorking = $isWorking;

        return $this;
    }

    public function getEnginCategory(): ?EnginCategory
    {
        return $this->enginCategory;
    }

    public function setEnginCategory(?EnginCategory $enginCategory): static
    {
        $this->enginCategory = $enginCategory;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function __toString(): string
    {
        return strtoupper($this->registration) . " " . $this->label;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwnerPseudo(): ?string
    {
        return $this->owner instanceof User ? $this->getOwner()->getPseudo() : $this->ownerPseudo;
    }

    public function getOwnerPhoneNumber(): ?string
    {
        return $this->owner instanceof User ? $this->getOwner()->getUsername() : $this->ownerPhoneNumber;
    }

    public function getRegistrationCipher(): ?string
    {
        return $this->registrationCipher;
    }

    public function setRegistrationCipher(string $registrationCipher): static
    {
        $this->registrationCipher = $registrationCipher;

        return $this;
    }
}
