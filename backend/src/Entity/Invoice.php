<?php

namespace App\Entity;

use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(name: 'invoices')]
#[ORM\HasLifecycleCallbacks]
class Invoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $number;

    #[ORM\Column(length: 255)]
    private string $supplierName;

    #[ORM\Column(length: 255)]
    private string $supplierTaxId;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $netAmount;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $vatAmount;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $grossAmount;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column(enumType: InvoiceStatus::class)]
    private InvoiceStatus $status;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $issueDate;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $dueDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $number,
        string $supplierName,
        string $supplierTaxId,
        string $netAmount,
        string $vatAmount,
        string $grossAmount,
        string $currency,
        \DateTimeImmutable $issueDate,
        \DateTimeImmutable $dueDate,
        InvoiceStatus $status = InvoiceStatus::Pending,
    ) {
        $this->id = Uuid::v7();
        $this->number = $number;
        $this->supplierName = $supplierName;
        $this->supplierTaxId = $supplierTaxId;
        $this->netAmount = $netAmount;
        $this->vatAmount = $vatAmount;
        $this->grossAmount = $grossAmount;
        $this->currency = $currency;
        $this->issueDate = $issueDate;
        $this->dueDate = $dueDate;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getSupplierName(): string
    {
        return $this->supplierName;
    }

    public function getSupplierTaxId(): string
    {
        return $this->supplierTaxId;
    }

    public function getNetAmount(): string
    {
        return $this->netAmount;
    }

    public function setNetAmount(string $netAmount): void
    {
        $this->netAmount = $netAmount;
    }

    public function getVatAmount(): string
    {
        return $this->vatAmount;
    }

    public function setVatAmount(string $vatAmount): void
    {
        $this->vatAmount = $vatAmount;
    }

    public function getGrossAmount(): string
    {
        return $this->grossAmount;
    }

    public function setGrossAmount(string $grossAmount): void
    {
        $this->grossAmount = $grossAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function getIssueDate(): \DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
