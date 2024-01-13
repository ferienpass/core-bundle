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

namespace Ferienpass\CoreBundle\Monolog\Context;

/**
 * Monolog context that is being added to log records resulting from message that returned a {NotificationHandlerResult}.
 */
class NotificationContext
{
    public function __construct(private readonly int $notification, private readonly int $message, private readonly array $tokens, private readonly string $language, private readonly bool $success)
    {
    }

    public function getNotification(): int
    {
        return $this->notification;
    }

    public function getMessage(): int
    {
        return $this->message;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }
}
