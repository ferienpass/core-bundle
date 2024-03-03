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

namespace Ferienpass\CoreBundle\Notification;

use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Export\Payments\ReceiptExportInterface;
use Ferienpass\CoreBundle\Notifier\Message\EmailMessage;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage as SymfonyEmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class PaymentCreatedNotification extends AbstractNotification implements NotificationInterface, EmailNotificationInterface
{
    private Payment $payment;

    public function __construct(private ReceiptExportInterface $receiptExport)
    {
        parent::__construct();
    }

    public static function getName(): string
    {
        return 'payment_created';
    }

    public function payment(Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'payment' => $this->payment,
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['payment']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?SymfonyEmailMessage
    {
        return EmailMessage::fromFerienpassNotification($this, $recipient, function (NotificationEmail $email) {
            $email->attachFromPath($this->receiptExport->generate($this->payment), sprintf('beleg-%s', $this->payment->getId()));
        });
    }
}
