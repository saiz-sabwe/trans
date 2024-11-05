<?php

namespace App\Entity;

use App\Repository\ItineraryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


//ALTER TABLE itinerary ADD CONSTRAINT constraint_unique_itinerary UNIQUE (from_stop, end_stop);
#[ORM\Entity(repositoryClass: ItineraryRepository::class)]
#[UniqueEntity(
    fields: ['fromStop', 'endStop'],
    message: 'Itinéraire déjà existant',
    errorPath: 'fromStop'
)]
class Itinerary
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?BusStop $fromStop = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?BusStop $endStop = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFromStop(): ?BusStop
    {
        return $this->fromStop;
    }

    public function setFromStop(?BusStop $fromStop): static
    {
        $this->fromStop = $fromStop;

        return $this;
    }

    public function getEndStop(): ?BusStop
    {
        return $this->endStop;
    }

    public function setEndStop(?BusStop $endStop): static
    {
        $this->endStop = $endStop;

        return $this;
    }

    public function __toString()
    {
        $from = $this->fromStop !== null ? $this->getFromStop()->getLabel() : null;
        $end = $this->endStop !== null ? $this->getEndStop()->getLabel() : null;

        if($from === null || $end === null)
        {
            return null;
        }

        return $from . " > " . $end;
    }
}
