<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /** @return list<Client> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('client')
            ->andWhere('client.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('client.company', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(int $id, User $owner): ?Client
    {
        return $this->createQueryBuilder('client')
            ->andWhere('client.id = :id')
            ->andWhere('client.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForUser(User $owner): int
    {
        return (int) $this->createQueryBuilder('client')
            ->select('COUNT(client.id)')
            ->andWhere('client.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
