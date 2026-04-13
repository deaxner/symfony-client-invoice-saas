<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed-demo-data', description: 'Seed demo users, clients, projects, and invoices with a four-year operating history.')]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement($platform->getTruncateTableSQL('invoice', true));
        $connection->executeStatement($platform->getTruncateTableSQL('project', true));
        $connection->executeStatement($platform->getTruncateTableSQL('client', true));
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $users = [
            $this->createUser('alex@example.com', 'Password123'),
            $this->createUser('jamie@example.com', 'Password123'),
        ];

        $timelineStart = (new \DateTimeImmutable('first day of this month midnight'))->modify('-48 months');
        $today = new \DateTimeImmutable('today');

        $companies = [
            ['Northwind Labs', 'Nina North', 'nina@northwind.test'],
            ['Bluepeak Studio', 'Ben Parker', 'ben@bluepeak.test'],
            ['Astera Retail', 'Ava Stone', 'ava@astera.test'],
            ['Harborline Ops', 'Hugo Lake', 'hugo@harborline.test'],
            ['Signal Forge', 'Sara Ford', 'sara@signalforge.test'],
            ['Orbit Ledger', 'Owen Bright', 'owen@orbitledger.test'],
            ['Cinder Systems', 'Cleo Voss', 'cleo@cinder.test'],
            ['Maple Health', 'Maya Reed', 'maya@maplehealth.test'],
            ['Tidal Commerce', 'Theo Hart', 'theo@tidal.test'],
            ['Lumen Transit', 'Leah Cole', 'leah@lumen.test'],
            ['Vector Works', 'Victor Snow', 'victor@vectorworks.test'],
            ['Pioneer Stack', 'Pia Long', 'pia@pioneerstack.test'],
        ];

        $clients = [];
        foreach ($companies as $index => [$company, $name, $email]) {
            $owner = $users[$index % count($users)];
            $client = (new Client())
                ->setOwner($owner)
                ->setCompany($company)
                ->setName($name)
                ->setEmail($email)
                ->setPhone(sprintf('+31 20 555 %04d', 1000 + $index))
                ->setAddress(sprintf('%d Market Street, Amsterdam', 10 + $index));

            $this->entityManager->persist($client);
            $clients[] = $client;
        }

        $projectBlueprints = [
            [Project::BILLING_HOURLY, '95.00', '42.00', null, null, null],
            [Project::BILLING_SLA, null, '38.00', '5400.00', 80, null],
            [Project::BILLING_FIXED_RETAINER, null, '45.00', null, null, '6800.00'],
            [Project::BILLING_HOURLY, '110.00', '48.00', null, null, null],
            [Project::BILLING_SLA, null, '41.00', '6200.00', 96, null],
            [Project::BILLING_FIXED_RETAINER, null, '52.00', null, null, '8200.00'],
            [Project::BILLING_HOURLY, '102.00', '44.00', null, null, null],
            [Project::BILLING_SLA, null, '39.00', '5100.00', 72, null],
            [Project::BILLING_FIXED_RETAINER, null, '47.00', null, null, '7200.00'],
            [Project::BILLING_HOURLY, '118.00', '53.00', null, null, null],
            [Project::BILLING_SLA, null, '43.00', '6600.00', 104, null],
            [Project::BILLING_FIXED_RETAINER, null, '55.00', null, null, '9100.00'],
        ];

        $projects = [];
        foreach ($clients as $index => $client) {
            [$billingModel, $hourlyRate, $costRate, $slaMonthlyFee, $hoursIncluded, $retainer] = $projectBlueprints[$index];
            $activeFrom = $timelineStart->modify(sprintf('+%d months', $index * 2));
            $isActive = $index !== 4;
            $activeUntil = $isActive ? null : $today->modify('-8 months');

            $project = (new Project())
                ->setOwner($client->getOwner())
                ->setClient($client)
                ->setName($client->getCompany() . ' Delivery')
                ->setCode(sprintf('PRJ-%02d', $index + 1))
                ->setBillingModel($billingModel)
                ->setHourlyRate($hourlyRate)
                ->setInternalCostRateDefault($costRate)
                ->setSlaMonthlyFee($slaMonthlyFee)
                ->setMonthlyHoursIncluded($hoursIncluded)
                ->setFixedMonthlyRetainer($retainer)
                ->setActiveFrom($activeFrom)
                ->setActiveUntil($activeUntil)
                ->setIsActive($isActive)
                ->setDescription('Long-running seeded commercial project with delivery, billing, and client history covering the last four years.');

            $this->entityManager->persist($project);
            $projects[] = $project;
        }

        $invoiceCount = 0;
        foreach ($projects as $projectIndex => $project) {
            $client = $project->getClient();
            $cursor = ($project->getActiveFrom() ?? $timelineStart)->modify('first day of this month');
            $end = ($project->getActiveUntil() ?? $today)->modify('first day of this month');

            while ($cursor <= $end) {
                $issuedAt = $cursor->modify(sprintf('+%d days', 2 + (($projectIndex + $invoiceCount) % 5)));
                $dueAt = $issuedAt->modify('+14 days');
                $amount = $this->resolveInvoiceAmount($project, $projectIndex, $invoiceCount, $cursor);
                $status = $this->resolveInvoiceStatus($dueAt, $today, $projectIndex, $invoiceCount);

                $invoice = (new Invoice())
                    ->setOwner($project->getOwner())
                    ->setClient($client)
                    ->setProject($project)
                    ->setNumber(sprintf('INV-%s-%s', $project->getCode(), $cursor->format('Ym')))
                    ->setAmount($amount)
                    ->setIssuedAt($issuedAt)
                    ->setDueAt($dueAt)
                    ->setStatus($status)
                    ->setDescription($this->buildInvoiceDescription($project, $cursor));

                $this->entityManager->persist($invoice);
                ++$invoiceCount;
                $cursor = $cursor->modify('+1 month');
            }
        }

        $this->entityManager->flush();

        $io->success([
            sprintf('Seeded 2 demo users, %d clients, %d projects, and %d invoices spanning the last four years.', count($clients), count($projects), $invoiceCount),
            'alex@example.com / Password123',
            'jamie@example.com / Password123',
        ]);

        return Command::SUCCESS;
    }

    private function createUser(string $email, string $plainPassword): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);

        return $user;
    }

    private function resolveInvoiceAmount(Project $project, int $projectIndex, int $invoiceCount, \DateTimeImmutable $cursor): string
    {
        return match ($project->getBillingModel()) {
            Project::BILLING_HOURLY => number_format(
                ((float) $project->getHourlyRate()) * (26 + (($projectIndex + (int) $cursor->format('n')) % 8) * 3),
                2,
                '.',
                ''
            ),
            Project::BILLING_SLA => number_format(
                ((float) $project->getSlaMonthlyFee()) + ((int) $cursor->format('Y') - 2022) * 150 + (($invoiceCount + $projectIndex) % 3) * 95,
                2,
                '.',
                ''
            ),
            Project::BILLING_FIXED_RETAINER => number_format(
                ((float) $project->getFixedMonthlyRetainer()) + (($projectIndex + (int) $cursor->format('m')) % 4) * 180,
                2,
                '.',
                ''
            ),
            default => '0.00',
        };
    }

    private function resolveInvoiceStatus(\DateTimeImmutable $dueAt, \DateTimeImmutable $today, int $projectIndex, int $invoiceCount): string
    {
        if ($dueAt >= $today->modify('-45 days')) {
            return Invoice::STATUS_UNPAID;
        }

        return (($projectIndex + $invoiceCount) % 11 === 0) ? Invoice::STATUS_UNPAID : Invoice::STATUS_PAID;
    }

    private function buildInvoiceDescription(Project $project, \DateTimeImmutable $cursor): string
    {
        return match ($project->getBillingModel()) {
            Project::BILLING_HOURLY => sprintf('Monthly delivery invoice for tracked implementation hours in %s.', $cursor->format('F Y')),
            Project::BILLING_SLA => sprintf('SLA coverage, incident response, and reporting for %s.', $cursor->format('F Y')),
            Project::BILLING_FIXED_RETAINER => sprintf('Retainer invoice covering roadmap delivery and stakeholder support in %s.', $cursor->format('F Y')),
            default => sprintf('Seeded invoice for %s.', $cursor->format('F Y')),
        };
    }
}
