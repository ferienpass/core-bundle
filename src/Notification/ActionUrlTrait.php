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

trait ActionUrlTrait
{
    private ?string $actionUrl = null;

    public function actionUrl(string $url): static
    {
        $this->actionUrl = $url;

        return $this;
    }
}
