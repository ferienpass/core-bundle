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
use Ferienpass\CoreBundle\Applications\UnconfirmedApplications;
use Ferienpass\CoreBundle\Message\ConfirmApplications;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WhenConfirmApplicationsThenNotify implements MessageHandlerInterface
{
    public function __construct(private UnconfirmedApplications $unconfirmedApplications, private TranslatorInterface $translator)
    {
    }

    public function __invoke(ConfirmApplications $message): ?NotificationHandlerResult
    {
        $notification = Notification::findOneByType('admission_letter');
        if (null === $notification) {
            throw new \RuntimeException('Missing notification for confirming applications!');
        }

        $result = [];

        foreach ($this->unconfirmedApplications->getUninformedMembers() as $uninformedMember) {
            $result[] = $this->sendNotificationToMember($notification, $uninformedMember);
        }

        foreach ($this->unconfirmedApplications->getUninformedParticipants() as $uninformedParticipant) {
            $result[] = $this->sendNotificationToParticipant($notification, $uninformedParticipant);
        }

        return new NotificationHandlerResult(array_merge(...$result));
    }

    private function sendNotificationToMember(Notification $notification, array $data): array
    {
        $data = array_values($data);

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        $tokens = $data;

        $first = reset($data[0]);

        $tokens['recipient_email'] = $first['member_email'];
        $tokens['recipient_firstname'] = $first['member_firstname'];
        $tokens['recipient_lastname'] = $first['member_lastname'];
        $tokens['link'] = null;
        $tokens['participants'] = $data;
        $tokens['copyright'] = $this->translator->trans('email.copyright', [], null, $language);
        $tokens['footer_reason'] = $this->translator->trans('email.reason.applied', [], null, $language);

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] =
                new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return $result;
    }

    private function sendNotificationToParticipant(Notification $notification, array $data): array
    {
        $data = array_values($data);

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        $tokens = $data;

        $first = reset($data);

        $tokens['recipient_email'] = $first['participant_email'];
        $tokens['recipient_firstname'] = $first['participant_firstname'];
        $tokens['recipient_lastname'] = $first['participant_lastname'];
        $tokens['link'] = null;
        $tokens['participants'] = [$data];
        $tokens['footer_reason'] = $this->translator->trans('email.reason.applied', [], null, $language);

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] =
                new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return $result;
    }
}
