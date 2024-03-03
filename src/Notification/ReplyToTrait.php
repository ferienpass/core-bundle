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

trait ReplyToTrait
{
    private ?string $replyTo = null;

    public function replyTo(string $replyTo): static
    {
        $this->replyTo = $replyTo;

        return $this;
    }
}
