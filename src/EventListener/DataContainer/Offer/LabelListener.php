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
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LabelListener
{
    public function __construct(private EditionRepository $editionRepository, private OfferRepository $offerRepository, private Connection $connection, private UrlGeneratorInterface $router)
    {
    }

    /**
     * @Callback(table="Offer", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        /** @var Offer|null $offer */
        $offer = $this->offerRepository->find($dc->id);
        if (null === $offer) {
            return;
        }
        if ($offer->isVariantBase()) {
            return;
        }

        $GLOBALS['TL_DCA']['Offer']['palettes']['default'] = '{admin_legend},varbase;{name_legend},name,alias;{date_legend},dates,applicationDeadline,comment;{status_legend},published,cancelled';
        $GLOBALS['TL_DCA']['Offer']['fields']['name']['eval']['readonly'] = true;
    }

    /**
     * @Callback(table="Offer", target="list.label.label")
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $labels): array
    {
        if ($row['edition']) {
            $edition = $this->editionRepository->find($row['edition']);
            $labels[0] = preg_replace('/[^A-Z0-9]/', '', $edition->getName());
        }

        /** @var Offer|null $offer */
        $offer = $this->offerRepository->find($row['id']);
        if (null === $offer) {
            return $labels;
        }

        $labels[1] = implode(', ', array_map(fn (Host $h) => mb_strimwidth($h->getName(), 0, 25, '…'), $offer->getHosts()->toArray()));

        $labels[2] = mb_strimwidth($labels[2], 0, 30, '…');

        if ($offer->isCancelled()) {
            $labels[2] = sprintf('<span title="abgesagt" class="inline-block mr-1 bg-red-100 rounded-sm leading-none text-xs text-red-900 p-1 font-medium">A</span><strike>%s</strike>', $labels[2]);
        }

        if ($offer->isVariant()) {
            $labels[2] = sprintf('<span title="Variante" class="inline-block mr-1 bg-yellow-100 rounded-sm leading-none text-xs text-yellow-900 p-1 font-medium">V</span>%s', $labels[2]);
        }

        /** @var OfferDate $date */
        if ($date = $offer->getDates()->first()) {
            $formatter = new \IntlDateFormatter('de_DE', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
            $formatter->setPattern('E, dd.MM. HH:mm');

            $labels[3] = $formatter->format($date->getBegin());
        }

        if ($row['onlineApplication']) {
            $labels[4] = sprintf('<span class="flex items-center"><span class="inline-block w-2 h-2 mr-1 rounded-full bg-%s-700"></span> %s / %s</span>', $offer->getMaxParticipants() && $offer->getAttendancesConfirmed()->count() >= $offer->getMaxParticipants() ? 'red' : 'green', $offer->getAttendancesNotWithdrawn()->count(), $offer->getMaxParticipants() ?: '&infin;');

            if ($offer->getAttendancesWaiting()->count() > 0) {
                $status = '<span title="wartende" class="inline-block mr-1 bg-yellow-100 rounded-sm leading-none text-xs text-yellow-900 p-1 font-medium">W</span>';
            } else {
                $status = '<span title="OK" class="inline-block mr-1 bg-green-100 rounded-sm leading-none text-xs text-green-900 p-1 font-medium">O</span>';
            }

            $labels[4] = sprintf('<span class="flex items-center justify-between"><span>%s</span>%s</span>', $labels[4], $status);
        }

        return $labels;
    }

    /**
     * @Callback(table="Offer", target="list.operations.attendances.button")
     *
     * @param mixed $href
     * @param mixed $label
     * @param mixed $title
     * @param mixed $icon
     * @param mixed $attributes
     */
    public function attendancesButton(array $row, $href, $label, $title, $icon, $attributes): string
    {
        if (!$row['onlineApplication']) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
        }

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            $this->router->generate('backend_offer_applications', ['id' => $row['id']]),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    /**
     * @Callback(table="Offer", target="config.onload")
     */
    public function onload(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        /** @var Offer|null $offer */
        $offer = $this->offerRepository->find($dc->id);
        if (null === $offer) {
            return;
        }
        if ($offer->isVariantBase()) {
            return;
        }

        unset(
            $GLOBALS['TL_DCA']['Offer']['fields']['name'],
            $GLOBALS['TL_DCA']['Offer']['fields']['description'],
            $GLOBALS['TL_DCA']['Offer']['fields']['meetingPoint'],
            $GLOBALS['TL_DCA']['Offer']['fields']['bring'],
            $GLOBALS['TL_DCA']['Offer']['fields']['minAge'],
            $GLOBALS['TL_DCA']['Offer']['fields']['maxAge'],
            $GLOBALS['TL_DCA']['Offer']['fields']['fee'],
            $GLOBALS['TL_DCA']['Offer']['fields']['accessibility'],
            $GLOBALS['TL_DCA']['Offer']['fields']['aktivPass'],
            $GLOBALS['TL_DCA']['Offer']['fields']['image'],
            $GLOBALS['TL_DCA']['Offer']['fields']['downloads'],
            $GLOBALS['TL_DCA']['Offer']['fields']['requiresApplication'],
            $GLOBALS['TL_DCA']['Offer']['fields']['onlineApplication'],
            $GLOBALS['TL_DCA']['Offer']['fields']['applyText'],
            $GLOBALS['TL_DCA']['Offer']['fields']['contact'],
        );
    }

    /**
     * @Callback(table="Offer", target="config.onsubmit")
     */
    public function onsubmit(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        /** @var Offer|null $offer */
        $offer = $this->offerRepository->find($dc->id);
        if (null === $offer) {
            return;
        }
        if ($offer->isVariant()) {
            return;
        }

        try {
            $this->connection->createQueryBuilder()
                ->update('Offer')
                ->set('name', '?')
                ->set('description', '?')
                ->set('meetingPoint', '?')
                ->set('bring', '?')
                ->set('minAge', '?')
                ->set('maxAge', '?')
                ->set('minParticipants', '?')
                ->set('maxParticipants', '?')
                ->set('fee', '?')
                ->set('accessibility', '?')
                ->set('aktivPass', '?')
                ->set('image', '?')
                ->set('downloads', '?')
                ->set('requiresApplication', '?')
                ->set('onlineApplication', '?')
                ->set('applyText', '?')
                ->set('contact', '?')
                ->setParameter(0, $offer->getName())
                ->setParameter(1, $offer->getDescription())
                ->setParameter(2, $offer->getMeetingPoint())
                ->setParameter(3, $offer->getBring())
                ->setParameter(4, $offer->getMinAge())
                ->setParameter(5, $offer->getMaxAge())
                ->setParameter(6, $offer->getMinParticipants())
                ->setParameter(7, $offer->getMaxParticipants())
                ->setParameter(8, $offer->getFee())
                ->setParameter(9, $offer->getAccessibility())
                ->setParameter(10, $offer->isAktivPass())
                ->setParameter(11, $offer->getImage())
                ->setParameter(12, $offer->getDownloads())
                ->setParameter(13, $offer->requiresApplication())
                ->setParameter(14, $offer->isOnlineApplication())
                ->setParameter(15, $offer->getApplyText())
                ->setParameter(16, $offer->getContact())
            ;
        } catch (\Exception) {
        }
    }
}
