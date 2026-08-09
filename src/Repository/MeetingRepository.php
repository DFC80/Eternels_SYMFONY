<?php

namespace App\Repository;

use App\Entity\Meeting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meeting>
 */
class MeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.date >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('m.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestPublishedAG(): ?Meeting
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.isPublished = true')
            ->setParameter('type', Meeting::TYPE_ASSEMBLEE_GENERALE)
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
