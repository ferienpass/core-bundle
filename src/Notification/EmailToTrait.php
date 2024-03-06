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

trait EmailToTrait
{
    private ?string $emailTo = null;

    public function emailTo(string $to): static
    {
        $this->emailTo = $to;

        return $this;
    }

    public function getEmailTo(): ?string
    {
        return $this->emailTo;
    }
}
