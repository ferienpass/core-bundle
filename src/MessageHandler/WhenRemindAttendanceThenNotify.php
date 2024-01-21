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

namespace Ferienpass\CoreBundle\MessageHandler;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Message\RemindAttendance;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class WhenRemindAttendanceThenNotify
{
    public function __construct(private readonly AttendanceRepository $attendanceRepository, private readonly ICalExport $iCal, private readonly TranslatorInterface $translator, private readonly ContaoFramework $framework)
    {
    }

    public function __invoke(RemindAttendance $message): ?NotificationHandlerResult
    {
        /** @var Attendance $attendance */
        $attendance = $this->attendanceRepository->find($message->getAttendance());
        if (null === $attendance) {
            return null;
        }

        if (null === $participant = $attendance->getParticipant()) {
            return null;
        }

        $this->framework->initialize();

        $notification = Notification::findOneByType('attendance_reminder');
        if (null === $notification) {
            throw new \RuntimeException('Missing notification for attendance reminder!');
        }

        $offer = $attendance->getOffer();

        return $this->sendNotification($notification, $participant, $offer);
    }

    private function sendNotification(Notification $notification, Participant $participant, Offer $offer): NotificationHandlerResult
    {
        $tokens = [];
        $tokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        $tokens['footer_reason'] = $this->translator->trans('email.reason.applied', [], null, $language);
        $tokens['copyright'] = $this->translator->trans('email.copyright', [], null, $language);
        $tokens['attachment'] = $this->iCal->generate([$offer]);

        $tokens['offer'] = $offer->getId();
        $tokens['participant'] = $participant->getId();

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] = new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return new NotificationHandlerResult($result);
    }
}
