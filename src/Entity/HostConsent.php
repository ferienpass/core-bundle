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

namespace Ferienpass\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Repository\HostConsentRepository;

#[ORM\Entity(repositoryClass: HostConsentRepository::class)]
class HostConsent extends Consent
{
    public static function fromText(string $text, User $user): self
    {
        return new self($user, md5($text));
    }
}
