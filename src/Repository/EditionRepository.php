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

use Contao\PageModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Edition;

class EditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Edition::class);
    }

    public function findOneToEdit(): ?Edition
    {
        return $this->findOneWithActiveTask('host_editing_stage');
    }

    public function findDefaultForHost(): ?Edition
    {
        return $this->findOneToEdit() ?: $this->findOneToShow() ?: $this->findOneClosestPastByTask('host_editing_stage');
    }

    /**
     * Find one to show in the frontend on the current page.
     */
    public function findOneToShow(PageModel $currentPage = null): ?Edition
    {
        $fallback = null;
        foreach ($this->findWithActiveTask('show_offers') as $passEdition) {
            if (null === $currentPage && null === $fallback) {
                $fallback = $passEdition;
            }
        }

        return $fallback;
    }

    public function findOneWithCurrentHoliday(): ?Edition
    {
        return $this->findOneWithActiveTask('holiday');
    }

    /**
     * @return array<Edition>
     */
    public function findCurrent(): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    "period.type = 'holiday'",
                    $qb0->expr()->orX(
                        $qb0->expr()->gte('period.periodBegin', 'CURRENT_TIMESTAMP()'),
                        $qb0->expr()->gte('period.periodEnd', ':end')
                    )
                )
            )
            ->orderBy('period.periodEnd', 'DESC')
            ->setParameter('end', new \DateTime('-1 year'), Types::DATETIME_MUTABLE)
            ->getQuery();

        return $qb->getResult();
    }

    /**
     * @return array<int, Edition>
     */
    public function findWithActiveTask(string $taskName): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->lte('period.periodBegin', 'CURRENT_TIMESTAMP()'),
                    $qb0->expr()->gte('period.periodEnd', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', $taskName)
            ->getQuery();

        return $qb->getResult();
    }

    /**
     * @return array<int, Edition>
     */
    public function findWithActiveAccessCodeStrategies(): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->isNotNull('period.accessCodeStrategy'),
                    $qb0->expr()->lte('period.periodBegin', 'CURRENT_TIMESTAMP()'),
                    $qb0->expr()->gte('period.periodEnd', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', 'application_system')
            ->getQuery();

        return $qb->getResult();
    }

    public function findOneWithActiveTask(string $taskName): ?Edition
    {
        return $this->findWithActiveTask($taskName)[0] ?? null;
    }

    public function findOneClosestByTask(string $taskName): ?Edition
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->gt('period.periodBegin', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', $taskName)
            ->orderBy('period.periodBegin', 'ASC')
            ->getQuery();

        try {
            return $qb->setMaxResults(1)->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneClosestPastByTask(string $taskName): ?Edition
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->lt('period.periodEnd', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', $taskName)
            ->orderBy('period.periodEnd', 'DESC')
            ->getQuery();

        try {
            return $qb->setMaxResults(1)->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findUpcoming(string $taskName = 'holiday'): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->gt('period.periodBegin', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', $taskName)
            ->orderBy('period.periodBegin', 'ASC')
            ->getQuery();

        return $qb->getResult();
    }

    public function findPrevious(string $taskName = 'holiday'): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('edition')
            ->innerJoin(
                'edition.tasks',
                'period',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->eq('period.type', ':period'),
                    $qb0->expr()->lt('period.periodEnd', 'CURRENT_TIMESTAMP()')
                )
            )
            ->setParameter('period', $taskName)
            ->orderBy('period.periodEnd', 'DESC')
            ->getQuery();

        return $qb->getResult();
    }
}
