<?php

namespace App\Entity;

use App\Repository\WalletOperationDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WalletOperationDetailRepository::class)]

class WalletOperationDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['api'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 20)]
    private ?string $payerAccountNumber = null;

    #[ORM\Column(length: 3)]
    private ?string $payerCurrency = null;

    #[ORM\Column(length: 30)]
    private ?string $payerOperator = null;

    #[ORM\Column(length: 40)]
    private ?string $makutaId = null;

    #[ORM\OneToOne(inversedBy: 'walletOperationDetail', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?WalletOperation $walletOperation = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPayerAccountNumber(): ?string
    {
        return $this->payerAccountNumber;
    }

    public function setPayerAccountNumber(string $payerAccountNumber): static
    {
        $this->payerAccountNumber = $payerAccountNumber;
        return $this;
    }

    public function getPayerCurrency(): ?string
    {
        return $this->payerCurrency;
    }

    public function setPayerCurrency(string $payerCurrency): static
    {
        $this->payerCurrency = $payerCurrency;
        return $this;
    }

    public function getPayerOperator(): ?string
    {
        return $this->payerOperator;
    }

    public function setPayerOperator(string $payerOperator): static
    {
        $this->payerOperator = $payerOperator;
        return $this;
    }

    public function getMakutaId(): ?string
    {
        return $this->makutaId;
    }

    public function setMakutaId(string $makutaId): static
    {
        $this->makutaId = $makutaId;
        return $this;
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
}

