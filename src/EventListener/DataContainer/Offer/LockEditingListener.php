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
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Repository\OfferRepository;

class LockEditingListener
{
    public function __construct(private OfferRepository $offerRepository)
    {
    }

    /**
     * @Callback(table="Offer", target="config.onload")
     */
    public function disableFields(?DataContainer $dc): void
    {
        if (null === $dc || !$dc->id) {
            return;
        }

        $offer = $this->offerRepository->find($dc->id);
        if (!$offer instanceof Offer) {
            return;
        }

        if ($offer->getAttendances()->isEmpty()) {
            return;
        }

        $GLOBALS['TL_DCA']['Offer']['fields']['edition']['eval']['disabled'] = true;
        $GLOBALS['TL_LANG']['Offer']['edition'][1] = 'Das Feld kann nicht geändert werden, sobald Kinder angemeldet sind.';
    }
}
