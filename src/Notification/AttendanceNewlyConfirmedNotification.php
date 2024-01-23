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

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class AttendanceNewlyConfirmedNotification extends Notification implements NotificationInterface, EmailNotificationInterface
{
    private Attendance $attendance;

    public function __construct(private readonly ICalExport $iCalExport)
    {
        parent::__construct();
    }

    public static function getName(): string
    {
        return 'attendance_newly_confirmed';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email', 'sms'];
    }

    public function attendance(Attendance $attendance): static
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $email = (new NotificationEmail(self::getName()))
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->content($this->getContent())
            ->attachFromPath($this->iCalExport->generate([$this->attendance->getOffer()]), $this->attendance->getOffer()->getAlias())
            ->context([
                'attendance' => $this->attendance,
            ])
        ;

        return new EmailMessage($email);
    }
}
