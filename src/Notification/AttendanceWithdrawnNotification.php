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
use Ferienpass\CoreBundle\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class AttendanceWithdrawnNotification extends AbstractNotification implements NotificationInterface, EditionAwareNotificationInterface, EmailNotificationInterface
{
    private Attendance $attendance;

    public static function getName(): string
    {
        return 'attendance_withdrawn';
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

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'attendance' => $this->attendance,
            'offer' => $this->attendance->getOffer(),
            'participant' => $this->attendance->getParticipant(),
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['attendance', 'offer', 'participant']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $email = (new NotificationEmail(self::getName()))
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->content($this->getContent())
            ->context($this->getContext())
        ;

        return new EmailMessage($email);
    }
}
