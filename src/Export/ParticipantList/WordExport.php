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

namespace Ferienpass\CoreBundle\Export\ParticipantList;

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\OfferExportInterface;
use PhpOffice\PhpWord\Exception\Exception as WordException;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class WordExport implements OfferExportInterface
{
    private Filesystem $filesystem;
    private NormalizerInterface $serializer;
    private ?string $templatePath;

    public function __construct(Filesystem $filesystem, NormalizerInterface $serializer, ?string $templatePath)
    {
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
        $this->templatePath = $templatePath;
    }

    public function generate(Offer $offer, string $destination = null): string
    {
        if (null === $this->templatePath) {
            throw new \LogicException('No Word template defined');
        }

        $docPath = $this->generateDocument($offer)->save();

        if (null !== $destination) {
            $this->filesystem->copy($docPath, $destination);
        }

        return $destination ?? $docPath;
    }

    private function generateDocument(Offer $offer): TemplateProcessor
    {
        $attendances = $offer->getAttendancesNotWithdrawn();
        $attendees = $attendances->filter(fn (Attendance $attendance) => 'confirmed' === $attendance->getStatus());
        $candidates = $attendances->filter(fn (Attendance $attendance) => 'waitlisted' === $attendance->getStatus());

        // Variables for template
        $normalizedOffer = $this->serializer->normalize($offer);
        $attendees = $this->serializer->normalize($attendees);
        $candidates = $this->serializer->normalize($candidates);
        $variables = array_combine(array_map(fn ($k) => 'offer.'.$k, array_keys($normalizedOffer)), $normalizedOffer);

        // Create DOCX template
        Settings::setOutputEscapingEnabled(true);
        $templateProcessor = new TemplateProcessor($this->templatePath);

        // Set the variables to replace in the template.
        foreach ($variables as $search => $replace) {
            $templateProcessor->setValue($search, $replace);
        }

        // Add participants
        try {
            $countRows = max(\count($attendees), $offer->getMaxParticipants());
            $prototype = array_fill_keys(array_keys($attendees[0]), '');

            // When too few attendees, fill up with empty rows
            $attendees += array_fill(array_key_last($attendees) + 1, 1 + $countRows - \count($attendees), $prototype);

            $templateProcessor->cloneRow('attendee.name', $countRows);

            $i = 0;
            foreach ($attendees as $attendee) {
                $templateProcessor->setValue(sprintf('attendee.nr#%d', $i), $i++);

                foreach ($attendee as $k => $v) {
                    $templateProcessor->setValue(sprintf('attendee.%s#%d', $k, $i), $v);
                }
            }

            $templateProcessor->cloneRow('candidate.name', \count($candidates));

            $i = 0;
            foreach ($candidates as $candidate) {
                $templateProcessor->setValue(sprintf('candidate.nr#%d', $i), $i++);

                foreach ($candidate as $k => $v) {
                    $templateProcessor->setValue(sprintf('candidate.%s#%d', $k, $i), $v);
                }
            }
        } catch (WordException $e) {
        }

        return $templateProcessor;
    }
}
