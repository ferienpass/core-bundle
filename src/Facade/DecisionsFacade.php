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

namespace Ferienpass\CoreBundle\Facade;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Query\Expr\Join;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Message\SendAttendanceDecisions;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;

/**
 * A decision, also called "Zulassungsbescheid", is sent out for every attendance after the "lot" procedure.
 */
class DecisionsFacade
{
    public function __construct(private readonly AttendanceRepository $repository)
    {
    }

    public function attendances(Edition $edition = null): array
    {
        $qb2 = $this->repository->createQueryBuilder('attendance');

        return $this->repository->createQueryBuilder('attendance')
            ->innerJoin('attendance.participant', 'participant')
            ->leftJoin('participant.user', 'user')
            ->innerJoin('attendance.offer', 'offer')
            ->leftJoin('offer.dates', 'dates')

            // Filter for edition
            ->andWhere('offer.edition = :edition')
            ->setParameter('edition', $edition)

            // Exclude attendances for that this message was already sent
            ->leftJoin('attendance.messengerLogs', 'message', Join::WITH, 'message.message = :sentDecisionsMessage AND message.createdAt >= attendance.modifiedAt')
            ->setParameter('sentDecisionsMessage', SendAttendanceDecisions::class)
            ->andWhere($qb2->expr()->isNull('message.id'))

            // Exclude attendance that were created during first come procedure and are waitlisted
            // (those do not get a notification but have immediate feedback)
            ->leftJoin('attendance.task', 'task', Join::WITH, "task.type = 'application_system'")
            ->andWhere($qb2->expr()->orX(
                'task IS NULL',
                "task.applicationSystem = 'lot'",
                $qb2->expr()->andX(
                    "task.applicationSystem <> 'firstcome'",
                    "attendance.status <> 'waitlisted'"
                )
            ))

            // Inform attendances that have any status but withdrawn and waiting
            ->andWhere('attendance.status NOT IN (:status)')
            ->setParameter('status', ['withdrawn', 'waiting'], ArrayParameterType::STRING)

            ->getQuery()
            ->getResult();
    }
}
