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
    private int $notification;
    private int $message;
    private array $tokens;
    private string $language;
    private bool $success;

    public function __construct(int $notification, int $message, array $tokens, string $language, bool $success)
    {
        $this->notification = $notification;
        $this->message = $message;
        $this->success = $success;
        $this->language = $language;
        $this->tokens = $tokens;
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
