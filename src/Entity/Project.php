<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'project')]
#[ORM\HasLifecycleCallbacks]
class Project
{
    public const BILLING_HOURLY = 'hourly';
    public const BILLING_SLA = 'sla';
    public const BILLING_FIXED_RETAINER = 'fixed_retainer';
    public const BILLING_MODELS = [
        self::BILLING_HOURLY,
        self::BILLING_SLA,
        self::BILLING_FIXED_RETAINER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 100, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 20)]
    private string $billingModel = self::BILLING_HOURLY;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $hourlyRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $internalCostRateDefault = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $slaMonthlyFee = null;

    #[ORM\Column(nullable: true)]
    private ?int $monthlyHoursIncluded = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $fixedMonthlyRetainer = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $activeFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $activeUntil = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Invoice> */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Invoice::class)]
    private Collection $invoices;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getBillingModel(): string
    {
        return $this->billingModel;
    }

    public function setBillingModel(string $billingModel): static
    {
        $this->billingModel = $billingModel;

        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?string $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getInternalCostRateDefault(): ?string
    {
        return $this->internalCostRateDefault;
    }

    public function setInternalCostRateDefault(?string $internalCostRateDefault): static
    {
        $this->internalCostRateDefault = $internalCostRateDefault;

        return $this;
    }

    public function getSlaMonthlyFee(): ?string
    {
        return $this->slaMonthlyFee;
    }

    public function setSlaMonthlyFee(?string $slaMonthlyFee): static
    {
        $this->slaMonthlyFee = $slaMonthlyFee;

        return $this;
    }

    public function getMonthlyHoursIncluded(): ?int
    {
        return $this->monthlyHoursIncluded;
    }

    public function setMonthlyHoursIncluded(?int $monthlyHoursIncluded): static
    {
        $this->monthlyHoursIncluded = $monthlyHoursIncluded;

        return $this;
    }

    public function getFixedMonthlyRetainer(): ?string
    {
        return $this->fixedMonthlyRetainer;
    }

    public function setFixedMonthlyRetainer(?string $fixedMonthlyRetainer): static
    {
        $this->fixedMonthlyRetainer = $fixedMonthlyRetainer;

        return $this;
    }

    public function getActiveFrom(): ?\DateTimeImmutable
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?\DateTimeImmutable $activeFrom): static
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    public function getActiveUntil(): ?\DateTimeImmutable
    {
        return $this->activeUntil;
    }

    public function setActiveUntil(?\DateTimeImmutable $activeUntil): static
    {
        $this->activeUntil = $activeUntil;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, Invoice> */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
