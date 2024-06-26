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

use Ferienpass\CoreBundle\Entity\MessengerLog;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Message\PaymentReceiptCreated;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenPaymentReceiptCreatedThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly PaymentRepository $payments)
    {
    }

    public function __invoke(PaymentReceiptCreated $message, MessengerLog $log): void
    {
        /** @var Payment $payment */
        $payment = $this->payments->find($message->getPaymentId());
        if (null === $payment || '' === (string) $payment->getBillingEmail()) {
            return;
        }

        $notification = $this->notifier->paymentCreated($payment);
        if (null === $notification) {
            return;
        }

        $this->notifier->send($notification->belongsTo($log), new Recipient($payment->getBillingEmail()));
    }
}
