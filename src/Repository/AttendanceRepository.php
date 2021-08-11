<?php

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Attendance;

class AttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attendance::class);
    }

    public function countParticipants(int $editionId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.participant) AS count')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function countAttendancesWithoutWithdrawn(int $editionId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id) AS count')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
