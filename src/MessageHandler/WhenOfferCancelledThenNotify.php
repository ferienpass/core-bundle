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
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\EventListener\Notification\GetNotificationTokensTrait;
use Ferienpass\CoreBundle\Message\OfferCancelled;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use NotificationCenter\Model\Notification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class WhenOfferCancelledThenNotify implements MessageHandlerInterface
{
    use GetNotificationTokensTrait;

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function __invoke(OfferCancelled $message): ?NotificationHandlerResult
    {
        $offer = $this->doctrine->getRepository(Offer::class)->find($message->getOfferId());
        if (null === $offer) {
            return null;
        }

        /** @var Notification $notification */
        $notification = Notification::findOneByType('offer_cancelled');
        if (null === $notification) {
            return null;
        }

        $result = [];
        foreach ($offer->getAttendances() as $attendance) {
            $participant = $attendance->getParticipant();
            if (null === $participant) {
                continue;
            }

            $tokens = self::getNotificationTokens($participant, $offer);
            $language = $GLOBALS['TL_LANGUAGE'];

            /** @var Notification|Model $notification */
            foreach ($notification->send($tokens, $language) as $messageId => $success) {
                $result[] = new NotificationContext((int) $notification->id, (int) $messageId, $tokens, $language, $success);
            }
        }

        return new NotificationHandlerResult($result);
    }
}
