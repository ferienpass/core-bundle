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

use Ferienpass\CoreBundle\Entity\EditionTask;

/**
 * An application system that is defined as Task
 * in the backend and has a defined begin and end period.
 */
interface TimedApplicationSystemInterface extends ApplicationSystemInterface
{
    public function withTask(EditionTask $task): self;

    public function getTask(): EditionTask;
}
