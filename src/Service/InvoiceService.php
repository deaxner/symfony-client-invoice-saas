<?php

namespace App\Service;

use App\DTO\InvoiceData;
use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @return list<Invoice> */
    public function listForUser(User $user): array
    {
        return $this->invoiceRepository->findByOwner($user);
    }

    public function getForUser(int $id, User $user): Invoice
    {
        $invoice = $this->invoiceRepository->findOneOwnedByUser($id, $user);
        if (!$invoice instanceof Invoice) {
            throw new NotFoundHttpException('Invoice not found.');
        }

        return $invoice;
    }

    public function create(User $user, InvoiceData $data): Invoice
    {
        $invoice = (new Invoice())
            ->setOwner($user)
            ->setNumber($this->generateInvoiceNumber($user));

        $this->hydrate($invoice, $data, $user);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    public function update(Invoice $invoice, InvoiceData $data, User $user): Invoice
    {
        $this->hydrate($invoice, $data, $user);
        $this->entityManager->flush();

        return $invoice;
    }

    public function delete(Invoice $invoice): void
    {
        $this->entityManager->remove($invoice);
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

    private function hydrate(Invoice $invoice, InvoiceData $data, User $user): void
    {
        $client = $this->clientRepository->findOneOwnedByUser((int) $data->clientId, $user);
        if (null === $client) {
            throw new NotFoundHttpException('Client not found for this invoice.');
        }

        $invoice
            ->setOwner($user)
            ->setClient($client)
            ->setAmount(number_format((float) $data->amount, 2, '.', ''))
            ->setIssuedAt(new \DateTimeImmutable((string) $data->issuedAt))
            ->setDueAt(new \DateTimeImmutable((string) $data->dueAt))
            ->setStatus((string) $data->status)
            ->setDescription($data->description);
    }

    private function generateInvoiceNumber(User $user): string
    {
        return sprintf(
            'INV-%s-%04d',
            (new \DateTimeImmutable())->format('YmdHis'),
            random_int(1000, 9999)
        );
    }
}
