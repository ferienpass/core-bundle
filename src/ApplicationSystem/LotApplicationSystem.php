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

/**
 * An application system that runs in the front end when the lot application procedure is active.
 */
class LotApplicationSystem extends AbstractApplicationSystem
{
    public function getType(): string
    {
        return 'lot';
    }
}
