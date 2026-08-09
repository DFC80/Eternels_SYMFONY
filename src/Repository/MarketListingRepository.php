<?php

namespace App\Repository;

use App\Entity\MarketListing;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketListing>
 */
class MarketListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketListing::class);
    }

    /**
     * Returns own listings (all statuses) + other members' ACTIVE listings.
     */
    public function findVisibleForUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.seller', 's')
            ->addSelect('s')
            ->where('m.seller = :user OR m.status = :active')
            ->setParameter('user', $user)
            ->setParameter('active', 'ACTIVE')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentActive(int $limit = 3): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.seller', 's')
            ->addSelect('s')
            ->where('m.status = :active')
            ->setParameter('active', 'ACTIVE')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :active')
            ->setParameter('active', 'ACTIVE')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
