<?php

namespace App\Repository;

use App\Entity\Membership;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Membership>
 */
class MembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Membership::class);
    }

    public function findByMember(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.member = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveActivityIds(User $user): array
    {
        $results = $this->createQueryBuilder('m')
            ->select('IDENTITY(m.activity) as activityId')
            ->andWhere('m.member = :user')
            ->andWhere('m.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'activityId');
    }
}
