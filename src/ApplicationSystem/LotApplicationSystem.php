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

namespace Ferienpass\CoreBundle\ApplicationSystem;

use Ferienpass\CoreBundle\Entity\Attendance;

class LotApplicationSystem extends AbstractApplicationSystem
{
    public function getType(): string
    {
        return 'lot';
    }

    protected function setStatus(Attendance $attendance): void
    {
        // The attendance status will always be set to "waiting" unless set differently.
        if (null !== $attendance->getStatus()) {
            return;
        }

        $attendance->setStatus(Attendance::STATUS_WAITING);
    }
}
