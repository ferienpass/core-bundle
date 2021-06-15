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

use Contao\MemberModel;
use Contao\Model;
use Ferienpass\CoreBundle\Message\AccountActivated;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use Haste\Util\Format;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class WhenAccountActivatedThenNotify implements MessageHandlerInterface
{
    public function __invoke(AccountActivated $message): ?NotificationHandlerResult
    {
        $memberModel = MemberModel::findByPk($message->getUserId());
        if (null === $memberModel) {
            return null;
        }

        $notification = Notification::findOneByType('member_activation');
        if (null === $notification) {
            throw new \RuntimeException('Missing notification for account activation for users!');
        }

        return $this->sendNotification($notification, $memberModel->row());
    }

    private function sendNotification(Notification $notification, array $data, array $tokens = []): NotificationHandlerResult
    {
        $tokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        foreach ($data as $k => $v) {
            $tokens['member_'.$k] = Format::dcaValue('tl_member', $k, $v);
        }

        $result = [];
        $language = $GLOBALS['TL_LANGUAGE'];

        /** @var Notification|Model $notification */
        foreach ($notification->send($tokens, $language) as $messageId => $success) {
            $result[] = new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
        }

        return new NotificationHandlerResult($result);
    }
}
