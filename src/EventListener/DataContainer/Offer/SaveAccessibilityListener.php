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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Offer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Offer;

class SaveAccessibilityListener
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    /**
     * @Callback(table="Offer", target="fields.accessibility.save")
     *
     * @param mixed $value
     */
    public function labelCallback($value, DataContainer $dc)
    {
        $offer = $this->doctrine->getRepository(Offer::class)->find($dc->id);
        if (null === $offer) {
            return null;
        }

        $em = $this->doctrine->getManager();

        if ($value) {
            $value = StringUtil::deserialize($value);
        } else {
            $value = null;
        }
        $offer->setAccessibility($value);

        $em->flush();

        return null;
    }
}
