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

use Ferienpass\CoreBundle\Repository\NotificationRepository;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class Notifier implements NotifierInterface
{
    /**
     * @var array<string, Notification>
     */
    private array $notifications;

    public function __construct(iterable $notifications, private readonly \Symfony\Component\Notifier\Notifier $notifier, private readonly NotificationRepository $notificationRepository)
    {
        $this->notifications = $notifications instanceof \Traversable ? iterator_to_array($notifications) : $notifications;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->notifications);
    }

    public function getNotificationNames(): array
    {
        return array_keys($this->notifications);
    }

    public function get(string $key): ?Notification
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException('');
        }

        $notification = $this->notifications[$key];

        $entity = $this->notificationRepository->findOneBy(['type' => $key]);
        if (!($entity instanceof Entity\Notification)) {
            return null;
        }

        $notification
            ->subject($entity->getEmailSubject())
            ->content($entity->getEmailText())
        ;

        return $notification;
    }

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        $this->notifier->send($notification, ...$recipients);
    }
}
