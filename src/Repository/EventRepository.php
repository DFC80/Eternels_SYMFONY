<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findUpcomingEvents(int $limit = 10, bool $includeBureauOnly = false): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.startDate >= :now')
            ->andWhere('e.status != :cancelled')
            ->setParameter('now', new \DateTime())
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('e.startDate', 'ASC')
            ->setMaxResults($limit);

        if (!$includeBureauOnly) {
            $qb->andWhere('e.bureauOnly = false');
        }

        return $qb->getQuery()->getResult();
    }

    public function findPastEvents(int $limit = 10, bool $includeBureauOnly = false): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.startDate < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.startDate', 'DESC')
            ->setMaxResults($limit);

        if (!$includeBureauOnly) {
            $qb->andWhere('e.bureauOnly = false');
        }

        return $qb->getQuery()->getResult();
    }
}
