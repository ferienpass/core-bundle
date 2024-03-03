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

namespace Ferienpass\CoreBundle\Notifier;

use Ferienpass\CoreBundle\Entity;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Notification\AbstractNotification;
use Ferienpass\CoreBundle\Notification\AccountActivatedNotification;
use Ferienpass\CoreBundle\Notification\AccountCreatedNotification;
use Ferienpass\CoreBundle\Notification\AccountRegistrationHelpNotification;
use Ferienpass\CoreBundle\Notification\AttendanceConfirmedNotification;
use Ferienpass\CoreBundle\Notification\AttendanceDecisions;
use Ferienpass\CoreBundle\Notification\AttendanceNewlyConfirmedNotification;
use Ferienpass\CoreBundle\Notification\AttendanceWithdrawnNotification;
use Ferienpass\CoreBundle\Notification\HostCreatedNotification;
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
     * @var array<string, AbstractNotification>
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

    public function accountRegistrationHelp(User $user): ?AccountRegistrationHelpNotification
    {
        $notification = $this->get(AccountRegistrationHelpNotification::getName());
        if (!$notification instanceof AccountRegistrationHelpNotification) {
            return null;
        }

        return $notification->user($user);
    }

    public function hostCreated(Host $host, User $user): ?HostCreatedNotification
    {
        $notification = $this->get(HostCreatedNotification::getName());
        if (!$notification instanceof HostCreatedNotification) {
            return null;
        }

        return $notification->host($host)->user($user);
    }

    public function userPassword(string $token, User $user): ?UserPasswordNotification
    {
        $notification = $this->get(UserPasswordNotification::getName());
        if (!$notification instanceof UserPasswordNotification) {
            return null;
        }

        return $notification->token($token)->user($user);
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

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        $this->notifier->send($notification, ...$recipients);
    }

    public function types(): array
    {
        return array_keys($this->notifications);
    }

    public function isActive(string $key, Edition $edition = null, bool $strict = false): bool
    {
        return null !== $this->get($key, $edition, $strict);
    }

    public function getClass(string $key): ?string
    {
        if (!\array_key_exists($key, $this->notifications)) {
            return null;
        }

        return $this->notifications[$key]::class;
    }

    public function createMock(string $key, string $subject, string $content): ?Notification
    {
        if (!\array_key_exists($key, $this->notifications)) {
            return null;
        }

        $notification = $this->notifications[$key];
        if (!$notification instanceof AbstractNotification) {
            return null;
        }

        $notification = $notification->createMock();

        $notification
            ->subject($subject)
            ->content($content)
        ;

        return $notification;
    }

    private function get(string $key, Edition $edition = null, bool $strict = false): ?Notification
    {
        if (!\array_key_exists($key, $this->notifications)) {
            return null;
        }

        $notification = clone $this->notifications[$key];

        $entity = $this->notificationRepository
            ->createQueryBuilder('n')
            ->where('n.type = :type')
            ->andWhere('n.disable = 0')
            ->setParameter('type', $key)
        ;

        if (null !== $edition) {
            $entity->setParameter('edition', $edition);
            if ($strict) {
                $entity->andWhere('n.edition = :edition');
            } else {
                $entity->addSelect('(CASE WHEN n.edition = :edition THEN 1 ELSE 0 END) AS HIDDEN mainSort')->addOrderBy('mainSort', 'DESC');
            }
        } elseif ($strict) {
            $entity->andWhere('n.edition IS NULL');
        }

        $entity = $entity->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!($entity instanceof Entity\Notification)) {
            return null;
        }

        $notification
            ->subject($entity->getEmailSubject() ?? '')
            ->content($entity->getEmailText() ?? '')
        ;

        if ($entity->getEmailReplyTo()) {
            $notification->replyTo($entity->getEmailReplyTo());
        }

        return $notification;
    }
}
