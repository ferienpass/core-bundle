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

namespace Ferienpass\CoreBundle\Export\Offer\ICal;

use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;
use Ferienpass\CoreBundle\Export\Offer\OffersExportInterface;
use Symfony\Component\Filesystem\Filesystem;

final class ICalExport implements OffersExportInterface
{
    public function generate(iterable $offers, string $destination = null): string
    {
        $tmpPath = sys_get_temp_dir().'/iCal';
        $iCalPath = sprintf('%s/%s.ics', $tmpPath, random_int(0, 10000));

        $filesystem = new Filesystem();
        $filesystem->mkdir($tmpPath);

        $filesystem->dumpFile($iCalPath, (string) $this->createICal($offers));

        if (null !== $destination) {
            $filesystem->copy($iCalPath, $destination);
        }

        return $destination ?? $iCalPath;
    }

    private function createICal(iterable $offers): Component
    {
        $calendar = new Calendar($this->eventsGenerator($offers));

        return (new CalendarFactory())->createCalendar($calendar);
    }

    /**
     * @param iterable<int, BaseOffer>
     */
    private function eventsGenerator(iterable $offers): \Generator
    {
        foreach ($offers as $offer) {
            foreach ($offer->getDates() as $date) {
                yield (new Event())
                    ->setSummary($offer->getName())
                    ->setDescription((string) $offer->getDescription())
                    ->setLocation(new Location((string) $offer->getMeetingPoint()))
                    ->setOccurrence(new TimeSpan(
                        new DateTime($date->getBegin(), false),
                        new DateTime($date->getEnd(), false)
                    ))
                ;
            }
        }
    }
}
