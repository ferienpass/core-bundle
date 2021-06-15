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

namespace Ferienpass\CoreBundle\CronJob;

use Contao\CoreBundle\Cron\Cron;
use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Doctrine\DBAL\Connection;
use Ferienpass\CoreBundle\EventListener\Notification\GetNotificationTokensTrait;
use Ferienpass\CoreBundle\Model\Attendance;
use NotificationCenter\Model\Notification;

/**
 * @CronJob("hourly")
 */
class TriggerCronRemindersListener
{
    use GetNotificationTokensTrait;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(string $scope): void
    {
        // Do not execute this cron job in the web scope
        if (Cron::SCOPE_WEB === $scope) {
            return;
        }

        // find attendances
        //   "offer IN(SELECT id FROM Offer WHERE published=1 AND id IN (SELECT item_id FROM tl_metamodel_offer_date WHERE start > {$time} AND start <= {$timeEnd}))"
        //                    .' AND id NOT IN (SELECT attendance FROM tl_ferienpass_attendance_notification WHERE tstamp<>0 AND notification=?)'
        //
    }

    private function sendAttendanceReminderNotifications(iterable $attendances, int $notificationId): void
    {
        /** @var Notification $notification */
        $notification = Notification::findByPk($notificationId);
        if (null === $notification) {
            return;
        }

        foreach ($attendances as $attendance) {
            $sent = $notification->send(
                self::getNotificationTokens($attendance->getParticipant(), $attendance->getOffer())
            );

            // Mark as sent
            //  $this->connection->executeQuery(
            //                            'INSERT INTO tl_ferienpass_attendance_notification (tstamp, attendance, notification)'.
            //                            " VALUES ({$time}, {$attendances->id}, {$notificationId})".
            //                            " ON DUPLICATE KEY UPDATE tstamp={$time}"
            //                        );
        }
    }
}
