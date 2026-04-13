<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;

class DashboardService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly InvoiceRepository $invoiceRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function build(User $user): array
    {
        $totalInvoices = $this->invoiceRepository->countForUser($user);
        $paidInvoices = $this->invoiceRepository->countPaidForUser($user);

        return [
            'stats' => [
                'clients' => $this->clientRepository->countForUser($user),
                'invoices' => $totalInvoices,
                'paidInvoices' => $paidInvoices,
                'unpaidInvoices' => max(0, $totalInvoices - $paidInvoices),
                'paidRevenue' => $this->invoiceRepository->sumPaidRevenueForUser($user),
            ],
            'recentInvoices' => $this->invoiceRepository->findRecentForUser($user),
            'invoiceStatuses' => [
                ['label' => 'Paid', 'value' => $paidInvoices, 'status' => Invoice::STATUS_PAID],
                ['label' => 'Unpaid', 'value' => max(0, $totalInvoices - $paidInvoices), 'status' => Invoice::STATUS_UNPAID],
            ],
        ];
    }
}
