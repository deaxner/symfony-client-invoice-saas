<?php

namespace App\Controller;

use App\DTO\ClientData;
use App\Entity\User;
use App\Form\ClientType;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientService $clientService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('client/index.html.twig', [
            'clients' => $clientService->listForUser($user),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientService $clientService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = new ClientData();
        $form = $this->createForm(ClientType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $clientService->create($user, $data);
            $this->addFlash('success', 'Client created successfully.');

            return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'New client',
            'submitLabel' => 'Create client',
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(int $id, ClientService $clientService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('client/show.html.twig', [
            'client' => $clientService->getForUser($id, $user),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ClientService $clientService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $client = $clientService->getForUser($id, $user);
        $data = (new ClientData());
        $data->name = $client->getName();
        $data->company = $client->getCompany();
        $data->email = $client->getEmail();
        $data->phone = $client->getPhone();
        $data->address = $client->getAddress();

        $form = $this->createForm(ClientType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientService->update($client, $data);
            $this->addFlash('success', 'Client updated successfully.');

            return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'Edit client',
            'submitLabel' => 'Save changes',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_client_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ClientService $clientService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $client = $clientService->getForUser($id, $user);

        if ($this->isCsrfTokenValid('delete_client_' . $client->getId(), (string) $request->request->get('_token'))) {
            $clientService->delete($client);
            $this->addFlash('success', 'Client deleted.');
        }

        return $this->redirectToRoute('app_client_index');
    }
}
