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

class FiguresController extends AbstractEditionStatsWidgetController
{
    public function __construct(private AttendanceRepository $attendanceRepository, private OfferRepository $offerRepository, private EditionRepository $editionRepository)
    {
    }

    public function __invoke(Edition $edition): Response
    {
        $preceding = $this->editionRepository->findPreceding($edition);

        return $this->render('@FerienpassCore/Backend/EditionStats/figures.html.twig', [
            'count_participants' => $this->countParticipants($edition),
            'count_participants_preceding' => $this->countParticipants($preceding),
            'count_offers' => $this->countOffers($edition),
            'count_offers_preceding' => $this->countOffers($preceding),
            'count_offers_no_variants' => $this->countOffersWithoutVariants($edition),
            'count_offers_no_variants_preceding' => $this->countOffersWithoutVariants($preceding),
            'count_hosts' => $this->countHostsWithOffer($edition),
            'count_hosts_preceding' => $this->countHostsWithOffer($preceding),
            'count_attendances' => $this->countAttendancesWithoutWithdrawn($edition),
            'count_attendances_preceding' => $this->countAttendancesWithoutWithdrawn($preceding),
        ]);
    }

    private function countParticipants(?Edition $edition): ?int
    {
        if (null === $edition) {
            return null;
        }

        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT COALESCE(IDENTITY(a.participant), a.participantId))')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $edition->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countAttendancesWithoutWithdrawn(?Edition $edition): ?int
    {
        if (null === $edition) {
            return null;
        }

        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $edition->getId())
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countOffers(?Edition $edition): ?int
    {
        if (null === $edition) {
            return null;
        }

        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $edition->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countOffersWithoutVariants(?Edition $edition): ?int
    {
        if (null === $edition) {
            return null;
        }

        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.variantBase IS NULL')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $edition->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countHostsWithOffer(?Edition $edition): ?int
    {
        if (null === $edition) {
            return null;
        }

        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(DISTINCT h.id) AS count')
            ->innerJoin('o.hosts', 'h')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $edition->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
