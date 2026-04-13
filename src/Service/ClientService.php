<?php

namespace App\Service;

use App\DTO\ClientData;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @return list<Client> */
    public function listForUser(User $user): array
    {
        return $this->clientRepository->findByOwner($user);
    }

    public function getForUser(int $id, User $user): Client
    {
        $client = $this->clientRepository->findOneOwnedByUser($id, $user);
        if (!$client instanceof Client) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $client;
    }

    public function create(User $user, ClientData $data): Client
    {
        $client = (new Client())->setOwner($user);
        $this->hydrate($client, $data);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $client;
    }

    public function update(Client $client, ClientData $data): Client
    {
        $this->hydrate($client, $data);
        $this->entityManager->flush();

        return $client;
    }

    public function delete(Client $client): void
    {
        $this->entityManager->remove($client);
        $this->entityManager->flush();
    }

    private function hydrate(Client $client, ClientData $data): void
    {
        $client
            ->setName((string) $data->name)
            ->setCompany((string) $data->company)
            ->setEmail((string) $data->email)
            ->setPhone($data->phone)
            ->setAddress($data->address);
    }
}
