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
use Ferienpass\CoreBundle\Entity\EditionTask;

class EditionTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EditionTask::class);
    }

    public function currentPayDays(): array
    {
        return $this->findActive('pay_days');
    }

    private function findActive(string $taskName): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('period')
            ->andWhere($qb0->expr()->eq('period.type', ':period'))
            ->andWhere($qb0->expr()->lte('period.periodBegin', 'CURRENT_TIMESTAMP()'))
            ->andWhere($qb0->expr()->gte('period.periodEnd', 'CURRENT_TIMESTAMP()'))
            ->setParameter('period', $taskName)
            ->getQuery()
        ;

        return $qb->getResult();
    }
}
