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

namespace Ferienpass\CoreBundle\RemoteEvent;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Payments\ReceiptNumberGenerator;
use Ferienpass\CoreBundle\Repository\PaymentRepository;
use Ferienpass\CoreBundle\Webhook\PmPaymentNotifyEvent;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('pmPayment')]
final class PmPaymentWebhookConsumer implements ConsumerInterface
{
    public function __construct(private readonly PaymentRepository $paymentRepository, private readonly EntityManagerInterface $entityManager, private readonly ReceiptNumberGenerator $receiptNumber)
    {
    }

    public function consume(RemoteEvent $event): void
    {
        if (!($event instanceof PmPaymentNotifyEvent)) {
            return;
        }

        $this->handleEvent($event);
    }

    private function handleEvent(PmPaymentNotifyEvent $transaction): void
    {
        /** @var Payment $payment */
        $payment = $this->paymentRepository->findOneBy(['pmPaymentTransactionId' => $transaction->getId()]);
        if (null === $payment) {
            return;
        }

        if ($payment->isFinalized()) {
            return;
        }

        if ($transaction->isSuccessful()) {
            $payment->setStatus(Payment::STATUS_PAID);
            $payment->setReceiptNumber($this->receiptNumber->generate());
        } else {
            $payment->setStatus(Payment::STATUS_FAILED);
        }

        $this->entityManager->flush();
    }
}
