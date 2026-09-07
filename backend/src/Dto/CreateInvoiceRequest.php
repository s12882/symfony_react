<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateInvoiceRequest
{
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $issueDate;

    #[Assert\NotBlank]
    #[Assert\Date]
    #[Assert\GreaterThanOrEqual(propertyPath: 'issueDate')]
    public string $dueDate;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public string $netAmount;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public string $vatAmount;

    #[Assert\NotBlank]
    public string $grossAmount;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $number;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $supplierName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $supplierTaxId;

    #[Assert\NotBlank]
    #[Assert\Currency]
    public string $currency;

    #[Assert\Callback]
    public function validateGrossAmount(ExecutionContextInterface $context): void
    {
        if (bccomp($this->grossAmount, bcadd($this->netAmount, $this->vatAmount, 2), 2) !== 0) {
            $context->buildViolation('The gross amount must equal net amount + vat amount.')
                ->atPath('grossAmount')
                ->addViolation();
        }
    }
}
