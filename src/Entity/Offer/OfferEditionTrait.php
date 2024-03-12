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

namespace Ferienpass\CoreBundle\Entity\Offer;

use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Entity\Edition;

trait OfferEditionTrait
{
    #[ORM\ManyToOne(targetEntity: Edition::class, inversedBy: 'offers')]
    #[ORM\JoinColumn(name: 'edition', referencedColumnName: 'id')]
    private ?Edition $edition = null;

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): void
    {
        $this->edition = $edition;
    }
}
