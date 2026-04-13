<?php

namespace App\DTO;

use App\Entity\Project;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectData
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $clientId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[A-Z0-9_-]+$/', message: 'Code must contain uppercase letters, numbers, dashes, or underscores.')]
    public ?string $code = null;

    #[Assert\NotBlank]
    #[Assert\Choice(Project::BILLING_MODELS)]
    public ?string $billingModel = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Hourly rate must be a valid decimal value.')]
    public ?string $hourlyRate = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Internal cost rate must be a valid decimal value.')]
    public ?string $internalCostRateDefault = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'SLA monthly fee must be a valid decimal value.')]
    public ?string $slaMonthlyFee = null;

    #[Assert\Positive]
    public ?int $monthlyHoursIncluded = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Monthly retainer must be a valid decimal value.')]
    public ?string $fixedMonthlyRetainer = null;

    public ?string $activeFrom = null;
    public ?string $activeUntil = null;

    public bool $isActive = true;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;
}
