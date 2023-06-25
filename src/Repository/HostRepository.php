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
use Ferienpass\CoreBundle\Entity\Host;

class HostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Host::class);
    }

    /** @return Host[] */
    public function findByMemberId(int $memberId): array
    {
        $query = $this->createQueryBuilder('h')
            ->select('h')
            ->innerJoin('h.memberAssociations', 'a')
            ->where('a.member=:id')
            ->setParameter('id', $memberId)
            ->getQuery()
        ;

        return $query->getResult();
    }
}
