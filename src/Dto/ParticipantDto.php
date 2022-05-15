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

namespace Ferienpass\CoreBundle\Dto;

use Ferienpass\CoreBundle\Entity\Participant;

interface ParticipantDto
{
    public static function fromEntity(Participant $participant = null): self;

    public function toEntity(Participant $participant = null): Participant;
}
