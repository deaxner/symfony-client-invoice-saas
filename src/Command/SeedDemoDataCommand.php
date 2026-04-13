<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed-demo-data', description: 'Seed demo users, clients, and invoices.')]
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
        $connection->executeStatement($platform->getTruncateTableSQL('client', true));
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $users = [
            $this->createUser('owner@example.com', 'Password123'),
            $this->createUser('finance@example.com', 'Password123'),
        ];

        $companies = [
            ['Northwind Labs', 'Nina North', 'nina@northwind.test'],
            ['Bluepeak Studio', 'Ben Parker', 'ben@bluepeak.test'],
            ['Astera Retail', 'Ava Stone', 'ava@astera.test'],
            ['Harborline Ops', 'Hugo Lake', 'hugo@harborline.test'],
            ['Signal Forge', 'Sara Ford', 'sara@signalforge.test'],
            ['Orbit Ledger', 'Owen Bright', 'owen@orbitledger.test'],
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

        $descriptions = [
            'Monthly retainer for delivery, support, and reporting.',
            'Implementation milestone billed after stakeholder sign-off.',
            'Quarterly maintenance and monitoring package.',
            'Design and product support for the current release window.',
        ];

        for ($index = 0; $index < 24; $index += 1) {
            $client = $clients[$index % count($clients)];
            $owner = $client->getOwner();
            $issuedAt = new \DateTimeImmutable(sprintf('-%d days', 7 + ($index * 3)));
            $dueAt = $issuedAt->modify('+14 days');
            $status = $index % 3 === 0 ? Invoice::STATUS_PAID : Invoice::STATUS_UNPAID;

            $invoice = (new Invoice())
                ->setOwner($owner)
                ->setClient($client)
                ->setNumber(sprintf('INV-DEMO-%04d', $index + 1))
                ->setAmount(number_format(450 + ($index * 37.5), 2, '.', ''))
                ->setIssuedAt($issuedAt)
                ->setDueAt($dueAt)
                ->setStatus($status)
                ->setDescription($descriptions[$index % count($descriptions)]);

            $this->entityManager->persist($invoice);
        }

        $this->entityManager->flush();

        $io->success([
            'Seeded 2 demo users, 6 clients, and 24 invoices.',
            'owner@example.com / Password123',
            'finance@example.com / Password123',
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
}
