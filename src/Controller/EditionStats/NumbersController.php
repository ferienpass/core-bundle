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

use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Response;

class NumbersController extends AbstractEditionStatsWidgetController
{
    private AttendanceRepository $attendanceRepository;
    private OfferRepository $offerRepository;
    private EditionRepository $editionRepository;

    public function __construct(AttendanceRepository $attendanceRepository, OfferRepository $offerRepository, EditionRepository $editionRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->offerRepository = $offerRepository;
        $this->editionRepository = $editionRepository;
    }

    public function __invoke(Edition $edition): Response
    {
        $preceding = $this->editionRepository->findPreceding($edition);

        return $this->render('@FerienpassCore/Backend/EditionStats/numbers.html.twig', [
            'count_participants' => $this->attendanceRepository->countParticipants($edition->getId()),
            'count_participants_preceding' => $this->attendanceRepository->countParticipants($preceding->getId()),
            'count_offers' => $this->offerRepository->countInEdition($edition->getId()),
            'count_offers_preceding' => $this->offerRepository->countInEdition($preceding->getId()),
            'count_offers_no_variants' => $this->offerRepository->countWithoutVariantsInEdition($edition->getId()),
            'count_offers_no_variants_preceding' => $this->offerRepository->countWithoutVariantsInEdition($preceding->getId()),
            'count_hosts' => $this->offerRepository->countHostsWithOfferInEdition($edition->getId()),
            'count_hosts_preceding' => $this->offerRepository->countHostsWithOfferInEdition($preceding->getId()),
            'count_attendances' => $this->attendanceRepository->countAttendancesWithoutWithdrawn($edition->getId()),
            'count_attendances_preceding' => $this->attendanceRepository->countAttendancesWithoutWithdrawn($preceding->getId()),
        ]);
    }
}
