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

namespace Ferienpass\CoreBundle\Cron;

use Contao\CoreBundle\Cron\Cron;
use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\EventLogRelated;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Message\RemindAttendance;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Repository\MessengerLogRepository;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @CronJob("hourly")
 */
class SendRemindersListener
{
    public function __construct(private readonly AttendanceRepository $attendanceRepository, private readonly MessageBusInterface $messageBus, private readonly MessengerLogRepository $eventLogRepository)
    {
    }

    public function __invoke(string $scope): void
    {
        if (Cron::SCOPE_WEB === $scope) {
            return;
        }

        $qb = $this->attendanceRepository->createQueryBuilder('a');
        $qb2 = $this->eventLogRepository->createQueryBuilder('el');

        // Notice period: 1.5 days
        $noticePeriod = new \DateTime();
        $noticePeriod->modify('+36 hours');

        /** @var Collection|iterable<Attendance> $attendances */
        $attendances = $qb
            ->innerJoin('a.offer', 'o')
            ->innerJoin('o.dates', 'd')

            // LEFT JOIN event logs, because we actually want to filter out attendances with event log record.
            ->leftJoin(
                EventLogRelated::class,
                'elr',
                Join::WITH,
                $qb->expr()->andX(
                    "elr.relatedTable = 'Attendance'",
                    'a.id = elr.relatedId',
                    $qb->expr()->in(
                        'elr.logEntry',
                        $qb2->select('el.id')->where('el.message = :message')->getDQL()
                    )
                )
            )
            ->setParameter('message', RemindAttendance::class)

            ->andWhere('a.status = :status')
            ->setParameter('status', Attendance::STATUS_CONFIRMED)

            ->andWhere('o.state = :state')
            ->setParameter('state', OfferInterface::STATE_PUBLISHED)

            // The offer must not be in the past
            ->andWhere('d.begin > CURRENT_TIMESTAMP()')

            // The offer must take place no earlier than the notice period
            ->andWhere('d.begin <= :date')
            ->setParameter('date', $noticePeriod, Types::DATETIME_MUTABLE)

            // To not send reminders twice, an event log must not be present.
            ->andWhere('elr.id IS NULL')

            ->getQuery()
            ->execute()
        ;

        foreach ($attendances as $attendance) {
            $this->messageBus->dispatch(new RemindAttendance($attendance->getId()));
        }
    }
}
