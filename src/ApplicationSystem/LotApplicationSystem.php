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
 * An application system that runs in the front end when the lot application procedure is active.
 */
class LotApplicationSystem extends AbstractApplicationSystem implements TimedApplicationSystemInterface
{
    private EditionTask $task;

    /**
     * @required
     */
    public function withTask(EditionTask $task): self
    {
        if ('application_system' !== $task->getType() || 'lot' !== $task->getApplicationSystem()) {
            throw new \InvalidArgumentException('Edition task must be type Lot application procedure');
        }

        $clone = clone $this;
        $clone->task = $task;

        return $clone;
    }

    public function getTask(): EditionTask
    {
        return $this->task;
    }
}
