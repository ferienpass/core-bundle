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

namespace Ferienpass\CoreBundle;

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Notification\AccountActivatedNotification;
use Ferienpass\CoreBundle\Notification\AccountCreatedNotification;
use Ferienpass\CoreBundle\Notification\AttendanceConfirmedNotification;
use Ferienpass\CoreBundle\Notification\AttendanceDecisions;
use Ferienpass\CoreBundle\Notification\AttendanceNewlyConfirmedNotification;
use Ferienpass\CoreBundle\Notification\AttendanceWithdrawnNotification;
use Ferienpass\CoreBundle\Notification\OfferCancelledNotification;
use Ferienpass\CoreBundle\Notification\OfferRelaunchedNotification;
use Ferienpass\CoreBundle\Notification\PaymentCreatedNotification;
use Ferienpass\CoreBundle\Notification\RemindAttendanceNotification;
use Ferienpass\CoreBundle\Notification\UserInvitationNotification;
use Ferienpass\CoreBundle\Notification\UserPasswordNotification;
use Ferienpass\CoreBundle\Repository\NotificationRepository;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class Notifier implements NotifierInterface
{
    /**
     * @var array<string, Notification>
     */
    private array $notifications;

    public function __construct(#[TaggedIterator('ferienpass.notification', defaultIndexMethod: 'getName')] iterable $notifications, private readonly NotifierInterface $notifier, private readonly NotificationRepository $notificationRepository)
    {
        $this->notifications = $notifications instanceof \Traversable ? iterator_to_array($notifications) : $notifications;
    }

    public function accountActivated(User $user): ?AccountActivatedNotification
    {
        $notification = $this->get(AccountActivatedNotification::getName());
        if (!$notification instanceof AccountActivatedNotification) {
            return null;
        }

        return $notification->user($user);
    }

    public function accountCreated(User $user): ?AccountCreatedNotification
    {
        $notification = $this->get(AccountCreatedNotification::getName());
        if (!$notification instanceof AccountCreatedNotification) {
            return null;
        }

        return $notification->user($user);
    }

    public function attendanceNewlyConfirmed(Attendance $attendance, Edition $edition = null): ?AttendanceNewlyConfirmedNotification
    {
        $notification = $this->get(AttendanceNewlyConfirmedNotification::getName(), $edition);
        if (!$notification instanceof AttendanceNewlyConfirmedNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function attendanceConfirmed(Attendance $attendance, Edition $edition = null): ?AttendanceConfirmedNotification
    {
        $notification = $this->get(AttendanceConfirmedNotification::getName(), $edition);
        if (!$notification instanceof AttendanceConfirmedNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function attendanceWithdrawn(Attendance $attendance, Edition $edition = null): ?AttendanceWithdrawnNotification
    {
        $notification = $this->get(AttendanceWithdrawnNotification::getName(), $edition);
        if (!$notification instanceof AttendanceWithdrawnNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function attendanceDecisions(...$attendances): ?AttendanceDecisions
    {
        $notification = $this->get(AttendanceDecisions::getName());
        if (!$notification instanceof AttendanceDecisions) {
            return null;
        }

        foreach ($attendances as $attendance) {
            $notification->attendance($attendance);
        }

        return $notification;
    }

    public function offerCancelled(Attendance $attendance, Edition $edition = null): ?OfferCancelledNotification
    {
        $notification = $this->get(OfferCancelledNotification::getName(), $edition);
        if (!$notification instanceof OfferCancelledNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function offerRelaunched(Attendance $attendance, Edition $edition = null): ?OfferRelaunchedNotification
    {
        $notification = $this->get(OfferRelaunchedNotification::getName(), $edition);
        if (!$notification instanceof OfferRelaunchedNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function paymentCreated(Payment $payment): ?PaymentCreatedNotification
    {
        $notification = $this->get(PaymentCreatedNotification::getName());
        if (!$notification instanceof PaymentCreatedNotification) {
            return null;
        }

        return $notification->payment($payment);
    }

    public function remindAttendance(Attendance $attendance, Edition $edition = null): ?RemindAttendanceNotification
    {
        $notification = $this->get(RemindAttendanceNotification::getName(), $edition);
        if (!$notification instanceof RemindAttendanceNotification) {
            return null;
        }

        return $notification->attendance($attendance);
    }

    public function userInvitation(User $user, Host $host, string $email): ?UserInvitationNotification
    {
        $notification = $this->get(UserInvitationNotification::getName());
        if (!$notification instanceof UserInvitationNotification) {
            return null;
        }

        return $notification->user($user)->host($host)->email($email);
    }

    public function userPassword(User $user): ?UserPasswordNotification
    {
        $notification = $this->get(UserPasswordNotification::getName());
        if (!$notification instanceof UserPasswordNotification) {
            return null;
        }

        return $notification->user($user);
    }

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        $this->notifier->send($notification, ...$recipients);
    }

    public function types(): array
    {
        return array_keys($this->notifications);
    }

    public function isActive(string $key, Edition $edition = null): bool
    {
        return null !== $this->get($key, $edition);
    }

    private function get(string $key, Edition $edition = null): ?Notification
    {
        if (!\array_key_exists($key, $this->notifications)) {
            return null;
        }

        $notification = $this->notifications[$key];

        $entity = $this->notificationRepository->findOneBy(['type' => $key, 'disable' => false]/* ['edition = '.(int) $edition?->getId() => 'DESC'] */);
        if (!($entity instanceof Entity\Notification)) {
            return null;
        }

        $notification
            ->subject($entity->getEmailSubject() ?? '')
            ->content($entity->getEmailText() ?? '')
        ;

        return $notification;
    }
}
