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

use Contao\Model;
use Ferienpass\CoreBundle\EventListener\Notification\GetNotificationTokensTrait;
use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WhenAttendanceConfirmedThenNotify implements MessageHandlerInterface
{
    use GetNotificationTokensTrait;

    private AttendanceRepository $attendanceRepository;
    private TranslatorInterface $translator;
    private ICalExport $iCal;

    public function __construct(AttendanceRepository $attendanceRepository, TranslatorInterface $translator, ICalExport $iCal)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->translator = $translator;
        $this->iCal = $iCal;
    }

    public function __invoke(AttendanceStatusChanged $message): ?NotificationHandlerResult
    {
        if (!$message->shallNotify()) {
            return null;
        }

        $attendance = $this->attendanceRepository->find($message->getAttendance());
        if (null === $attendance || !$attendance->isConfirmed()) {
            return null;
        }

        $offer = $attendance->getOffer();
        $participant = $attendance->getParticipant();
        if (null === $participant) {
            return null;
        }

        /** @var Notification $notification */
        $notification = Notification::findOneByType('attendance_changed_confirmed');
        if (null === $notification) {
            return null;
        }

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        $tokens = self::getNotificationTokens($participant, $offer);

        $tokens['footer_reason'] = $this->translator->trans('email.reason.applied', [], null, $language);
        $tokens['copyright'] = $this->translator->trans('email.copyright', [], null, $language);
        $tokens['attachment'] = $this->iCal->generate([$offer]);

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] =
                new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return new NotificationHandlerResult($result);
    }
}
