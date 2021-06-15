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

interface ApplicationSystemInterface
{
    /**
     * Assign the attendance status to the status.
     * The application system will set the status that is considered being appropriate in this procedure.
     */
    public function assignStatus(Attendance $attendance): void;
}
