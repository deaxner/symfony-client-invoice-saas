<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/public-api/commercial')]
class PublicCommercialApiController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProjectService $projectService,
        private readonly InvoiceRepository $invoiceRepository,
    ) {
    }

    #[Route('', name: 'public_api_commercial_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $email = mb_strtolower((string) $request->query->get('email', ''));
        $user = '' === $email ? null : $this->userRepository->findOneBy(['email' => $email]);

        if (!$user instanceof User) {
            return $this->json([
                'data' => [
                    'projects' => [],
                    'clients' => [],
                    'summary' => [
                        'paidRevenue' => '0.00',
                        'unpaidExposure' => '0.00',
                    ],
                ],
            ]);
        }

        $projects = array_map(
            fn ($project): array => $this->projectService->serialize($project),
            $this->projectService->listForUser($user),
        );
        $invoices = $this->invoiceRepository->findByOwner($user);
        $clientRollups = [];
        $projectRollups = [];

        foreach ($projects as $project) {
            $clientId = $project['client']['id'] ?? null;
            if (null === $clientId) {
                continue;
            }

            $clientRollups[$clientId] = [
                'id' => $clientId,
                'name' => $project['client']['name'] ?? '',
                'company' => $project['client']['company'] ?? ($project['client']['name'] ?? 'Unknown'),
                'paidRevenue' => $clientRollups[$clientId]['paidRevenue'] ?? '0.00',
                'unpaidExposure' => $clientRollups[$clientId]['unpaidExposure'] ?? '0.00',
            ];
        }

        foreach ($invoices as $invoice) {
            $client = $invoice->getClient();
            $clientId = $client?->getId() ?? 0;
            $amount = (float) $invoice->getAmount();
            $clientRollups[$clientId] = [
                'id' => $clientId,
                'name' => $client?->getName() ?? '',
                'company' => $client?->getCompany() ?? 'Unknown',
                'paidRevenue' => number_format((float) ($clientRollups[$clientId]['paidRevenue'] ?? 0) + ('paid' === $invoice->getStatus() ? $amount : 0), 2, '.', ''),
                'unpaidExposure' => number_format((float) ($clientRollups[$clientId]['unpaidExposure'] ?? 0) + ('unpaid' === $invoice->getStatus() ? $amount : 0), 2, '.', ''),
            ];

            if (null !== $invoice->getProject()) {
                $projectKey = $invoice->getProject()->getCode();
                $projectRollups[$projectKey] = [
                    'paidRevenue' => number_format((float) ($projectRollups[$projectKey]['paidRevenue'] ?? 0) + ('paid' === $invoice->getStatus() ? $amount : 0), 2, '.', ''),
                    'unpaidExposure' => number_format((float) ($projectRollups[$projectKey]['unpaidExposure'] ?? 0) + ('unpaid' === $invoice->getStatus() ? $amount : 0), 2, '.', ''),
                ];
            }
        }

        $projects = array_map(function (array $project) use ($projectRollups): array {
            $rollup = $projectRollups[$project['code']] ?? ['paidRevenue' => '0.00', 'unpaidExposure' => '0.00'];

            return array_merge($project, $rollup);
        }, $projects);

        return $this->json([
            'data' => [
                'projects' => $projects,
                'clients' => array_values($clientRollups),
                'summary' => [
                    'paidRevenue' => $this->invoiceRepository->sumPaidRevenueForUser($user),
                    'unpaidExposure' => $this->invoiceRepository->sumUnpaidRevenueForUser($user),
                ],
            ],
        ]);
    }
}
