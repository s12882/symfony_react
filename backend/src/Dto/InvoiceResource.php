<?php

namespace App\Dto;

use App\Entity\Invoice;

final class InvoiceResource
{
    public static function fromEntity(Invoice $invoice): array
    {
        return [
            'id' => $invoice->getId(),
            'number' => $invoice->getNumber(),
            'supplier_name' => $invoice->getSupplierName(),
            'supplier_tax_id' => $invoice->getSupplierTaxId(),
            'net_amount' => $invoice->getNetAmount(),
            'vat_amount' => $invoice->getVatAmount(),
            'gross_amount' => $invoice->getGrossAmount(),
            'currency' => $invoice->getCurrency(),
            'status' => $invoice->getStatus()->value,
            'issue_date' => $invoice->getIssueDate()->format('Y-m-d'),
            'due_date' => $invoice->getDueDate()->format('Y-m-d'),
            'created_at' => $invoice->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $invoice->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
