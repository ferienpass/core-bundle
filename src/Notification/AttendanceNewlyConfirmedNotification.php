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

namespace Ferienpass\CoreBundle\Notification;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;
use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Notifier\Message\EmailMessage;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage as SymfonyEmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AttendanceNewlyConfirmedNotification extends AbstractNotification implements NotificationInterface, EditionAwareNotificationInterface, EmailNotificationInterface
{
    private Attendance $attendance;

    public function __construct(private readonly ICalExport $iCalExport, private readonly EventDispatcherInterface $dispatcher, private readonly ContaoFramework $contaoFramework)
    {
        parent::__construct();
    }

    public static function getName(): string
    {
        return 'attendance_newly_confirmed';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        //        if ($recipient instanceof SmsRecipientInterface && $recipient->getPhone()) {
        //            return ['email', 'sms'];
        //        }

        return ['email'];
    }

    public function attendance(Attendance $attendance): static
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'attendance' => $this->attendance,
            'offer' => $this->attendance->getOffer(),
            'participant' => $this->attendance->getParticipant(),
            'offer_fee' => $this->attendance->getOffer()->getFeePayable($this->attendance->getParticipant(), $this->dispatcher),
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['attendance']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?SymfonyEmailMessage
    {
        $this->contaoFramework->initialize();

        return EmailMessage::fromFerienpassNotification($this, $recipient, function (NotificationEmail $email) {
            /** @var BaseOffer $offer */
            $offer = $this->attendance->getOffer();

            $email->attachFromPath($this->iCalExport->generate([$offer]), $offer->getAlias());

            if (null !== $offer->getAgreementLetter() && null !== $file = FilesModel::findByPk($offer->getAgreementLetter())) {
                $email->attachFromPath($file->getAbsolutePath(), $file->name);
            }
        });
    }
}
