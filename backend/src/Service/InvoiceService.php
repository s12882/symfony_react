<?php

namespace App\Service;

use App\Dto\CreateInvoiceRequest;
use App\Dto\UpdateInvoiceRequest;
use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use App\Exception\InvoiceNotEditableException;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class InvoiceService
{
    public const PER_PAGE = 15;

    public function __construct(
        private readonly InvoiceRepository $invoices,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function list(?InvoiceStatus $status, int $page): Paginator
    {
        return $this->invoices->paginate($page, self::PER_PAGE, $status);
    }

    public function create(CreateInvoiceRequest $dto): Invoice
    {
        if (null !== $this->invoices->findByNumber($dto->number)) {
            throw new ValidationFailedException($dto, new ConstraintViolationList([
                new ConstraintViolation(
                    'This invoice number is already in use.',
                    null,
                    [],
                    $dto,
                    'number',
                    $dto->number,
                ),
            ]));
        }

        $invoice = new Invoice(
            number: $dto->number,
            supplierName: $dto->supplierName,
            supplierTaxId: $dto->supplierTaxId,
            netAmount: $dto->netAmount,
            vatAmount: $dto->vatAmount,
            grossAmount: $dto->grossAmount,
            currency: $dto->currency,
            issueDate: new \DateTimeImmutable($dto->issueDate),
            dueDate: new \DateTimeImmutable($dto->dueDate),
        );

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    public function update(Invoice $invoice, UpdateInvoiceRequest $dto): Invoice
    {
        if (InvoiceStatus::Pending !== $invoice->getStatus()) {
            throw new InvoiceNotEditableException();
        }

        $dueDate = new \DateTimeImmutable($dto->dueDate);

        if ($dueDate < $invoice->getIssueDate()) {
            throw new ValidationFailedException($dto, new ConstraintViolationList([
                new ConstraintViolation(
                    'Due date must be on or after the invoice issue date.',
                    null,
                    [],
                    $dto,
                    'dueDate',
                    $dto->dueDate,
                ),
            ]));
        }

        $invoice->setNetAmount($dto->netAmount);
        $invoice->setVatAmount($dto->vatAmount);
        $invoice->setGrossAmount(bcadd($dto->netAmount, $dto->vatAmount, 2));
        $invoice->setDueDate($dueDate);

        $this->entityManager->flush();

        return $invoice;
    }
}
