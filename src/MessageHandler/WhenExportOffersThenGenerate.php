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

namespace Ferienpass\CoreBundle\MessageHandler;

use Ferienpass\CoreBundle\Export\Offer\OfferExporter;
use Ferienpass\CoreBundle\Message\ExportOffers;
use Ferienpass\CoreBundle\Notification\DownloadNotification;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class WhenExportOffersThenGenerate
{
    public function __construct(private readonly OfferRepositoryInterface $repository, private readonly OfferExporter $exporter, private readonly Notifier $notifier, private readonly UrlGeneratorInterface $urlGenerator, private readonly UriSigner $uriSigner, private readonly DownloadNotification $notification)
    {
    }

    public function __invoke(ExportOffers $message): void
    {
        $offers = $this->repository->findBy(['id' => $message->getOfferIds()]);
        if (empty($offers)) {
            return;
        }

        $file = $this->exporter->getExporter($message->getExportKey())->generate($offers);
        $url = $this->urlGenerator->generate('admin_download', ['file' => base64_encode($file)], UrlGeneratorInterface::ABSOLUTE_URL);

        $notification = clone $this->notification;
        $this->notifier->send(
            $notification->subject('Download')->filename(basename($file))->actionUrl($this->uriSigner->sign($url)),
            new Recipient($message->getRecipient())
        );
    }
}
