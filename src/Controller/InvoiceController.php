<?php

namespace App\Controller;

use App\DTO\InvoiceData;
use App\Entity\User;
use App\Form\InvoiceType;
use App\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoices')]
class InvoiceController extends AbstractController
{
    #[Route('', name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceService $invoiceService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceService->listForUser($user),
        ]);
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, InvoiceService $invoiceService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = new InvoiceData();
        $data->issuedAt = (new \DateTimeImmutable())->format('Y-m-d');
        $data->dueAt = (new \DateTimeImmutable('+14 days'))->format('Y-m-d');
        $data->status = 'unpaid';
        $form = $this->createForm(InvoiceType::class, $data, [
            'client_choices' => $invoiceService->clientChoicesForUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice = $invoiceService->create($user, $data);
            $this->addFlash('success', 'Invoice created successfully.');

            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('invoice/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'New invoice',
            'submitLabel' => 'Create invoice',
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(int $id, InvoiceService $invoiceService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoiceService->getForUser($id, $user),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, InvoiceService $invoiceService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $invoice = $invoiceService->getForUser($id, $user);
        $data = new InvoiceData();
        $data->clientId = $invoice->getClient()?->getId();
        $data->amount = $invoice->getAmount();
        $data->issuedAt = $invoice->getIssuedAt()?->format('Y-m-d');
        $data->dueAt = $invoice->getDueAt()?->format('Y-m-d');
        $data->status = $invoice->getStatus();
        $data->description = $invoice->getDescription();

        $form = $this->createForm(InvoiceType::class, $data, [
            'client_choices' => $invoiceService->clientChoicesForUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoiceService->update($invoice, $data, $user);
            $this->addFlash('success', 'Invoice updated successfully.');

            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('invoice/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'Edit invoice',
            'submitLabel' => 'Save changes',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, InvoiceService $invoiceService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $invoice = $invoiceService->getForUser($id, $user);

        if ($this->isCsrfTokenValid('delete_invoice_' . $invoice->getId(), (string) $request->request->get('_token'))) {
            $invoiceService->delete($invoice);
            $this->addFlash('success', 'Invoice deleted.');
        }

        return $this->redirectToRoute('app_invoice_index');
    }
}
