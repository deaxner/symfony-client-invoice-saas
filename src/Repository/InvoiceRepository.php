<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /** @return list<Invoice> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('invoice')
            ->addSelect('client')
            ->join('invoice.client', 'client')
            ->andWhere('invoice.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('invoice.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(int $id, User $owner): ?Invoice
    {
        return $this->createQueryBuilder('invoice')
            ->addSelect('client')
            ->join('invoice.client', 'client')
            ->andWhere('invoice.id = :id')
            ->andWhere('invoice.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForUser(User $owner): int
    {
        return (int) $this->createQueryBuilder('invoice')
            ->select('COUNT(invoice.id)')
            ->andWhere('invoice.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPaidForUser(User $owner): int
    {
        return (int) $this->createQueryBuilder('invoice')
            ->select('COUNT(invoice.id)')
            ->andWhere('invoice.owner = :owner')
            ->andWhere('invoice.status = :status')
            ->setParameter('owner', $owner)
            ->setParameter('status', Invoice::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumPaidRevenueForUser(User $owner): string
    {
        return (string) ($this->createQueryBuilder('invoice')
            ->select('COALESCE(SUM(invoice.amount), 0)')
            ->andWhere('invoice.owner = :owner')
            ->andWhere('invoice.status = :status')
            ->setParameter('owner', $owner)
            ->setParameter('status', Invoice::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult() ?? '0.00');
    }

    /** @return list<Invoice> */
    public function findRecentForUser(User $owner, int $limit = 5): array
    {
        return $this->createQueryBuilder('invoice')
            ->addSelect('client')
            ->join('invoice.client', 'client')
            ->andWhere('invoice.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('invoice.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
