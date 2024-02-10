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

class AttendanceDecisions extends AbstractNotification implements NotificationInterface, EmailNotificationInterface
{
    /** @var array<int, array<int, Attendance>> */
    private array $attendances;

    public static function getName(): string
    {
        return 'attendance_decisions';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function attendance(Attendance $attendance): static
    {
        $this->attendances[$attendance->getParticipant()->getId()][] = $this->attendances;

        return $this;
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'attendance' => $this->attendances,
        ]);
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
