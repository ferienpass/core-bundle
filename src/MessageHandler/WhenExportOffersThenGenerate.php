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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class WhenExportOffersThenGenerate
{
    public function __construct(private readonly OfferRepositoryInterface $offers, private readonly OfferExporter $exporter, private readonly Notifier $notifier, private readonly UrlGeneratorInterface $urlGenerator, private readonly UriSigner $uriSigner, private readonly DownloadNotification $notification, #[Autowire('%kernel.project_dir%/%contao.upload_path%/Export')] private readonly string $destination)
    {
    }

    public function __invoke(ExportOffers $message): void
    {
        $offers = $this->offers->findBy(['id' => $message->getOfferIds()]);
        if (empty($offers)) {
            return;
        }

        $destination = sprintf('%s/%s-Angebote-%s', $this->destination, date('Y-m-d'), uniqid());
        $file = $this->exporter->getExporter($message->getExportKey())->generate($offers, $destination);
        $url = $this->urlGenerator->generate('admin_download', ['file' => base64_encode($file)], UrlGeneratorInterface::ABSOLUTE_URL);

        $notification = clone $this->notification;
        $this->notifier->send(
            $notification->subject('Download')->filename(basename($file))->actionUrl($this->uriSigner->sign($url)),
            new Recipient($message->getRecipient())
        );
    }
}
