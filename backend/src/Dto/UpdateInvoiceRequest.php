<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateInvoiceRequest
{
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $dueDate;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public string $netAmount;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public string $vatAmount;
}
