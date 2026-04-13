<?php

namespace App\DTO;

use App\Entity\Invoice;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceData
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $clientId = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Amount must be a valid decimal value.')]
    public ?string $amount = null;

    #[Assert\NotBlank]
    public ?string $issuedAt = null;

    #[Assert\NotBlank]
    public ?string $dueAt = null;

    #[Assert\NotBlank]
    #[Assert\Choice([Invoice::STATUS_PAID, Invoice::STATUS_UNPAID])]
    public ?string $status = null;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;
}
