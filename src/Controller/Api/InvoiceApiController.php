<?php

namespace App\Controller\Api;

use App\DTO\InvoiceData;
use App\Entity\Invoice;
use App\Entity\User;
use App\Form\InvoiceType;
use App\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/invoices')]
class InvoiceApiController extends AbstractController
{
    #[Route('', name: 'api_invoice_index', methods: ['GET'])]
    public function index(InvoiceService $invoiceService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(['data' => array_map([$this, 'serializeInvoice'], $invoiceService->listForUser($user))]);
    }

    #[Route('/{id}', name: 'api_invoice_show', methods: ['GET'])]
    public function show(int $id, InvoiceService $invoiceService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(['data' => $this->serializeInvoice($invoiceService->getForUser($id, $user))]);
    }

    #[Route('', name: 'api_invoice_create', methods: ['POST'])]
    public function create(Request $request, InvoiceService $invoiceService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $this->submit($request, new InvoiceData(), $invoiceService, $user);
        $invoice = $invoiceService->create($user, $data);

        return $this->json(['data' => $this->serializeInvoice($invoice)], 201);
    }

    #[Route('/{id}', name: 'api_invoice_update', methods: ['PUT'])]
    public function update(int $id, Request $request, InvoiceService $invoiceService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $invoice = $invoiceService->getForUser($id, $user);
        $data = $this->submit($request, new InvoiceData(), $invoiceService, $user);
        $invoiceService->update($invoice, $data, $user);

        return $this->json(['data' => $this->serializeInvoice($invoice)]);
    }

    #[Route('/{id}', name: 'api_invoice_delete', methods: ['DELETE'])]
    public function delete(int $id, InvoiceService $invoiceService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $invoice = $invoiceService->getForUser($id, $user);
        $invoiceService->delete($invoice);

        return $this->json(['data' => ['deleted' => true]]);
    }

    private function submit(Request $request, InvoiceData $data, InvoiceService $invoiceService, User $user): InvoiceData
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $form = $this->createForm(InvoiceType::class, $data, [
            'client_choices' => $invoiceService->clientChoicesForUser($user),
            'project_choices' => $invoiceService->projectChoicesForUser($user),
            'csrf_protection' => false,
        ]);
        $form->submit($payload);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                $field = $origin?->getName() ?? 'form';
                $errors[$field][] = $error->getMessage();
            }

            throw new \InvalidArgumentException(json_encode($errors, JSON_THROW_ON_ERROR));
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function serializeInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->getId(),
            'number' => $invoice->getNumber(),
            'client' => [
                'id' => $invoice->getClient()?->getId(),
                'name' => $invoice->getClient()?->getName(),
                'company' => $invoice->getClient()?->getCompany(),
            ],
            'project' => $invoice->getProject() ? [
                'id' => $invoice->getProject()?->getId(),
                'name' => $invoice->getProject()?->getName(),
                'code' => $invoice->getProject()?->getCode(),
            ] : null,
            'amount' => $invoice->getAmount(),
            'status' => $invoice->getStatus(),
            'issuedAt' => $invoice->getIssuedAt()?->format('Y-m-d'),
            'dueAt' => $invoice->getDueAt()?->format('Y-m-d'),
            'description' => $invoice->getDescription(),
        ];
    }
}
