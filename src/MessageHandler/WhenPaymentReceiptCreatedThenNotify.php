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

use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\EventListener\Notification\GetNotificationTokensTrait;
use Ferienpass\CoreBundle\Message\PaymentReceiptCreated;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Notification\PaymentCreatedNotification;
use Ferienpass\CoreBundle\Notifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenPaymentReceiptCreatedThenNotify
{
    use GetNotificationTokensTrait;

    public function __construct(private Notifier $notifier, private ManagerRegistry $doctrine)
    {
    }

    public function __invoke(PaymentReceiptCreated $message): ?NotificationHandlerResult
    {
        $payment = $this->doctrine->getRepository(Payment::class)->find($message->getPaymentId());
        if (null === $payment || '' === (string) $payment->getBillingEmail()) {
            return null;
        }

        if (!$this->notifier->has('payment_created')) {
            return null;
        }

        if (!(($notification = $this->notifier->get('payment_created')) instanceof PaymentCreatedNotification)) {
            return null;
        }

        $notification = $notification->withPayment($payment);

        $recipient = new Recipient($payment->getBillingEmail());

        $this->notifier->send($notification, $recipient);

        return null;
    }
}
