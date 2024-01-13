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

use Contao\CoreBundle\OptIn\OptIn;
use Contao\MemberModel;
use Contao\Model;
use Contao\OptInModel;
use Ferienpass\CoreBundle\Message\AccountResendActivation;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use Haste\Util\Format;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class WhenAccountResendNotificationThenNotify implements MessageHandlerInterface
{
    public function __construct(private readonly OptIn $optIn, private readonly RouterInterface $router)
    {
    }

    public function __invoke(AccountResendActivation $message): ?NotificationHandlerResult
    {
        $memberModel = MemberModel::findByPk($message->getUserId());
        if (null === $memberModel) {
            return null;
        }

        $optInToken = null;

        foreach (OptInModel::findByRelatedTableAndIds('tl_member', [$memberModel->id]) as $model) {
            // Look for a valid, unconfirmed token
            if (($token = $this->optIn->find($model->token)) && $token->isValid() && !$token->isConfirmed()) {
                $optInToken = $token;
                break;
            }
        }

        if (null === $optInToken) {
            return null;
        }

        $data = $memberModel->row();

        $optInToken = $this->optIn->create('reg-', $memberModel->email, ['tl_member' => [$memberModel->id]]);

        $tokens = [
            'link' => $this->router->generate('registration_activate', ['token' => $optInToken->getIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $notification = Notification::findOneByType('member_registration');
        if (null === $notification) {
            throw new \RuntimeException('Missing notification for account registration for users!');
        }

        return $this->sendNotification($notification, $data, $tokens);
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
