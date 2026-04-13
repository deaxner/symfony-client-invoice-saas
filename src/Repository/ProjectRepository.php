<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /** @return list<Project> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('project')
            ->addSelect('client')
            ->join('project.client', 'client')
            ->andWhere('project.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('project.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(int $id, User $owner): ?Project
    {
        return $this->createQueryBuilder('project')
            ->addSelect('client')
            ->join('project.client', 'client')
            ->andWhere('project.id = :id')
            ->andWhere('project.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForUser(User $owner): int
    {
        return (int) $this->createQueryBuilder('project')
            ->select('COUNT(project.id)')
            ->andWhere('project.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveForUser(User $owner): int
    {
        return (int) $this->createQueryBuilder('project')
            ->select('COUNT(project.id)')
            ->andWhere('project.owner = :owner')
            ->andWhere('project.isActive = :active')
            ->setParameter('owner', $owner)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<int, array{billingModel: string, total: int}> */
    public function countGroupedByBillingModel(User $owner): array
    {
        return $this->createQueryBuilder('project')
            ->select('project.billingModel AS billingModel, COUNT(project.id) AS total')
            ->andWhere('project.owner = :owner')
            ->setParameter('owner', $owner)
            ->groupBy('project.billingModel')
            ->getQuery()
            ->getArrayResult();
    }
}
