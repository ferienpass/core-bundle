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

namespace Ferienpass\CoreBundle\Message;

/**
 * A loggable message is a message that is persisted into the database.
 */
interface LoggableMessageInterface
{
    /**
     * Returns the related entries, e.g. [ 'tl_member' => 5 ] that implies, that those message
     * concerns that entity (e.g. account of member ID 5 was deleted).
     *
     * @return array<string, int>
     */
    public function getRelated(): array;
}
