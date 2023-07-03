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
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class PaymentCreatedNotification extends Notification implements EmailNotificationInterface
{
    private Payment $payment;

    public function __construct(private ReceiptExportInterface $receiptExport)
    {
        parent::__construct();
    }

    public function withPayment(Payment $payment): self
    {
        $clone = clone $this;

        $clone->payment = $payment;

        return $clone;
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        //        $email = NotificationEmail::asPublicEmail()
        //            ->theme('@FerienpassCore/Email/attendance_new.html.twig')
        //            ->to($recipient->getEmail())
        //            ->subject($this->getSubject() ?: 'test')
        //            ->content($this->getContent() ?: 'test')
        //            ->attachFromPath()
        //            ->action('Sign in', 'asdf')
        //        ;

        $email = (new Email())
            ->to($recipient->getEmail())
            ->from('ferienpass@badoldesloe.de')
            ->subject($this->getSubject())
            ->text($this->getContent())
            ->attachFromPath($this->receiptExport->generate($this->payment), sprintf('beleg-%s', $this->payment->getId()))
        ;

        return new EmailMessage($email);
    }
}
