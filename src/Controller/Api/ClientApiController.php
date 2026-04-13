<?php

namespace App\Controller\Api;

use App\DTO\ClientData;
use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/clients')]
class ClientApiController extends AbstractController
{
    #[Route('', name: 'api_client_index', methods: ['GET'])]
    public function index(ClientService $clientService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(['data' => array_map([$this, 'serializeClient'], $clientService->listForUser($user))]);
    }

    #[Route('/{id}', name: 'api_client_show', methods: ['GET'])]
    public function show(int $id, ClientService $clientService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(['data' => $this->serializeClient($clientService->getForUser($id, $user))]);
    }

    #[Route('', name: 'api_client_create', methods: ['POST'])]
    public function create(Request $request, ClientService $clientService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $this->submit($request, new ClientData(), ClientType::class);
        $client = $clientService->create($user, $data);

        return $this->json(['data' => $this->serializeClient($client)], 201);
    }

    #[Route('/{id}', name: 'api_client_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ClientService $clientService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $client = $clientService->getForUser($id, $user);
        $data = $this->submit($request, new ClientData(), ClientType::class);
        $clientService->update($client, $data);

        return $this->json(['data' => $this->serializeClient($client)]);
    }

    #[Route('/{id}', name: 'api_client_delete', methods: ['DELETE'])]
    public function delete(int $id, ClientService $clientService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $client = $clientService->getForUser($id, $user);
        $clientService->delete($client);

        return $this->json(['data' => ['deleted' => true]]);
    }

    private function submit(Request $request, ClientData $data, string $type): ClientData
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $form = $this->createForm($type, $data, ['csrf_protection' => false]);
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
    private function serializeClient(Client $client): array
    {
        return [
            'id' => $client->getId(),
            'name' => $client->getName(),
            'company' => $client->getCompany(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'address' => $client->getAddress(),
        ];
    }
}
