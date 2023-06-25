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

use Symfony\Component\Notifier\Notification\Notification;

class Notifier
{
    /**
     * @var array<string, Notification>
     */
    private array $notifications;

    public function __construct(iterable $notifications)
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
}
