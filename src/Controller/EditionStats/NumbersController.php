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

namespace Ferienpass\CoreBundle\Controller\EditionStats;

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Response;

class NumbersController extends AbstractEditionStatsWidgetController
{
    public function __construct(private AttendanceRepository $attendanceRepository, private OfferRepository $offerRepository, private EditionRepository $editionRepository)
    {
    }

    public function __invoke(Edition $edition): Response
    {
        $preceding = $this->editionRepository->findPreceding($edition);

        return $this->render('@FerienpassCore/Backend/EditionStats/numbers.html.twig', [
            'count_participants' => $this->countParticipants($edition->getId()),
            'count_participants_preceding' => $this->countParticipants($preceding->getId()),
            'count_offers' => $this->countOffers($edition->getId()),
            'count_offers_preceding' => $this->countOffers($preceding->getId()),
            'count_offers_no_variants' => $this->countOffersWithoutVariants($edition->getId()),
            'count_offers_no_variants_preceding' => $this->countOffersWithoutVariants($preceding->getId()),
            'count_hosts' => $this->countHostsWithOffer($edition->getId()),
            'count_hosts_preceding' => $this->countHostsWithOffer($preceding->getId()),
            'count_attendances' => $this->countAttendancesWithoutWithdrawn($edition->getId()),
            'count_attendances_preceding' => $this->countAttendancesWithoutWithdrawn($preceding->getId()),
        ]);
    }

    private function countParticipants(int $editionId): int
    {
        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT COALESCE(IDENTITY(a.participant), a.participantId))')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    private function countAttendancesWithoutWithdrawn(int $editionId): int
    {
        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    private function countOffers(int $editionId): int
    {
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    private function countOffersWithoutVariants(int $editionId): int
    {
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.variantBase IS NULL')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    private function countHostsWithOffer(int $editionId): int
    {
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(DISTINCT h.id) AS count')
            ->innerJoin('o.hosts', 'h')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $editionId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
