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
use Ferienpass\CoreBundle\Entity\Offer;

class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function countInEdition(int $editionId): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function countWithoutVariantsInEdition(int $editionId): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.variantBase IS NULL')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function countHostsWithOfferInEdition(int $editionId): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(DISTINCT h.id) AS count')
            ->innerJoin('o.hosts', 'h')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
