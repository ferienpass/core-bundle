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

namespace Ferienpass\CoreBundle\Message;

class ExportOffers
{
    public function __construct(private readonly string $exportKey, private readonly array $offerIds, private readonly string $recipient)
    {
    }

    public function getExportKey(): string
    {
        return $this->exportKey;
    }

    public function getOfferIds(): array
    {
        return $this->offerIds;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }
}
