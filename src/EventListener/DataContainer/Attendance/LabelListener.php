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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Attendance;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LabelListener
{
    private EditionRepository $editionRepository;
    private OfferRepository $offerRepository;
    private Connection $connection;
    private UrlGeneratorInterface $router;
    private TranslatorInterface $translator;

    public function __construct(EditionRepository $editionRepository, OfferRepository $offerRepository, Connection $connection, UrlGeneratorInterface $router, TranslatorInterface $translator)
    {
        $this->editionRepository = $editionRepository;
        $this->offerRepository = $offerRepository;
        $this->connection = $connection;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @Callback(table="Attendance", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        $GLOBALS['TL_DCA']['Attendance']['list']['sorting']['filter'] = [['participant_id=?', $dc->id]];
    }

    /**
     * @Callback(table="Attendance", target="list.label.label")
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $labels)
    {
        /** @var Offer $offer */
        $offer = $this->offerRepository->find($row['offer_id']);

        $labels[0] = sprintf('%s<br><span class="tl_gray">%s</span>', $offer->getName(), $offer->getDates()->first()->getBegin()->format('d.m.Y H:i'));

        switch ($row['status']) {
            case Attendance::STATUS_CONFIRMED:
                $labels[1] = sprintf(
                    '<span class="inline-flex items-center px-2.5 -ml-2.5 py-1 rounded-md text-base font-medium leading-5 bg-green-100 text-green-800">%s</span>',
                    $this->translator->trans('applications.status.confirmed.title')
                );
                break;
            case Attendance::STATUS_WAITLISTED:
                $labels[1] = sprintf(
                    '<span class="inline-flex items-center px-2.5 -ml-2.5 py-1 rounded-md text-base font-medium leading-5 bg-yellow-100 text-yellow-800">%s</span>',
                    $this->translator->trans('applications.status.waitlisted.title')
                );
                break;
            case Attendance::STATUS_WITHDRAWN:
                $labels[1] = sprintf(
                    '<span class="inline-flex items-center px-2.5 -ml-2.5 py-1 rounded-md text-base font-medium leading-5 bg-red-100 text-red-800">%s</span>',
                    $this->translator->trans('applications.status.withdrawn.title')
                );
                break;
            case Attendance::STATUS_ERROR:
                $labels[1] = sprintf(
                    '<span class="inline-flex items-center px-2.5 -ml-2.5 py-1 rounded-md text-base font-medium leading-5 bg-red-100 text-red-800">%s</span>',
                    $this->translator->trans('applications.status.error.title')
                );
                break;
            case Attendance::STATUS_WAITING:
                $labels[1] = sprintf(
                    '<span class="inline-flex items-center px-2.5 -ml-2.5 py-1 rounded-md text-base font-medium leading-5 bg-gray-100 text-gray-800">%s</span>',
                    $this->translator->trans('applications.status.waiting.title')
                );
                break;
        }

        return $labels;
    }

    /**
     * @Callback(table="Attendance", target="list.operations.attendances.button")
     *
     * @param mixed $href
     * @param mixed $label
     * @param mixed $title
     * @param mixed $icon
     * @param mixed $attributes
     */
    public function attendancesButton(array $row, $href, $label, $title, $icon, $attributes): string
    {
        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            $this->router->generate('backend_offer_applications', ['id' => $row['offer_id']]),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }
}
