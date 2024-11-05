<?php

namespace App\Entity;

use App\Repository\BusStopRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BusStopRepository::class)]
class BusStop
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    #[ORM\Column]
    private ?bool $isWorking = null;

    public function __construct()
    {
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

    public function isWorking(): ?bool
    {
        return $this->isWorking;
    }

    public function setWorking(bool $isWorking): static
    {
        $this->isWorking = $isWorking;

        return $this;
    }

    public function __toString()
    {
        return $this->label;
    }
}
