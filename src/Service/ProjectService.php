<?php

namespace App\Service;

use App\DTO\ProjectData;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @return list<Project> */
    public function listForUser(User $user): array
    {
        return $this->projectRepository->findByOwner($user);
    }

    public function getForUser(int $id, User $user): Project
    {
        $project = $this->projectRepository->findOneOwnedByUser($id, $user);
        if (!$project instanceof Project) {
            throw new NotFoundHttpException('Project not found.');
        }

        return $project;
    }

    public function create(User $user, ProjectData $data): Project
    {
        $project = (new Project())
            ->setOwner($user);

        $this->hydrate($project, $data, $user);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

    public function update(Project $project, ProjectData $data, User $user): Project
    {
        $this->hydrate($project, $data, $user);
        $this->entityManager->flush();

        return $project;
    }

    public function delete(Project $project): void
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }

    /** @return array<string, int> */
    public function clientChoicesForUser(User $user): array
    {
        $choices = [];
        foreach ($this->clientRepository->findByOwner($user) as $client) {
            $choices[$client->getCompany() . ' - ' . $client->getName()] = (int) $client->getId();
        }

        return $choices;
    }

    /** @return array<string, mixed> */
    public function serialize(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'code' => $project->getCode(),
            'billingModel' => $project->getBillingModel(),
            'hourlyRate' => $project->getHourlyRate(),
            'internalCostRateDefault' => $project->getInternalCostRateDefault(),
            'slaMonthlyFee' => $project->getSlaMonthlyFee(),
            'monthlyHoursIncluded' => $project->getMonthlyHoursIncluded(),
            'fixedMonthlyRetainer' => $project->getFixedMonthlyRetainer(),
            'activeFrom' => $project->getActiveFrom()?->format('Y-m-d'),
            'activeUntil' => $project->getActiveUntil()?->format('Y-m-d'),
            'isActive' => $project->isActive(),
            'description' => $project->getDescription(),
            'client' => [
                'id' => $project->getClient()?->getId(),
                'name' => $project->getClient()?->getName(),
                'company' => $project->getClient()?->getCompany(),
            ],
        ];
    }

    private function hydrate(Project $project, ProjectData $data, User $user): void
    {
        $client = $this->clientRepository->findOneOwnedByUser((int) $data->clientId, $user);
        if (null === $client) {
            throw new NotFoundHttpException('Client not found for this project.');
        }

        $project
            ->setOwner($user)
            ->setClient($client)
            ->setName((string) $data->name)
            ->setCode(mb_strtoupper((string) $data->code))
            ->setBillingModel((string) $data->billingModel)
            ->setHourlyRate($this->normalizeDecimal($data->hourlyRate))
            ->setInternalCostRateDefault($this->normalizeDecimal($data->internalCostRateDefault))
            ->setSlaMonthlyFee($this->normalizeDecimal($data->slaMonthlyFee))
            ->setMonthlyHoursIncluded($data->monthlyHoursIncluded)
            ->setFixedMonthlyRetainer($this->normalizeDecimal($data->fixedMonthlyRetainer))
            ->setActiveFrom($this->parseDate($data->activeFrom))
            ->setActiveUntil($this->parseDate($data->activeUntil))
            ->setIsActive($data->isActive)
            ->setDescription($data->description);
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    private function normalizeDecimal(?string $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
