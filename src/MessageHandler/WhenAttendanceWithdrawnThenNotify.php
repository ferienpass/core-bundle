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
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WhenAttendanceWithdrawnThenNotify implements MessageHandlerInterface
{
    private ManagerRegistry $doctrine;
    private TranslatorInterface $translator;

    public function __construct(ManagerRegistry $doctrine, TranslatorInterface $translator)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
    }

    public function __invoke(AttendanceStatusChanged $message): ?NotificationHandlerResult
    {
        if (!$message->shallNotify()) {
            return null;
        }

        $attendance = $this->doctrine->getRepository(Attendance::class)->find($message->getAttendance());
        if (null === $attendance || !$attendance->isWithdrawn()) {
            return null;
        }

        $offer = $attendance->getOffer();
        $participant = $attendance->getParticipant();
        if (null === $participant) {
            return null;
        }

        /** @var Notification $notification */
        $notification = Notification::findOneByType('attendance_changed_withdrawn');
        if (null === $notification) {
            return null;
        }

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        $tokens = [];

        $tokens['offer'] = $offer->getId();
        $tokens['participant'] = $participant->getId();

        $tokens['footer_reason'] = $this->translator->trans('email.reason.applied', [], null, $language);
        $tokens['copyright'] = $this->translator->trans('email.copyright', [], null, $language);

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] = new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return new NotificationHandlerResult($result);
    }
}
