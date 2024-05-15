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

namespace Ferienpass\CoreBundle\Webhook;

use Symfony\Component\RemoteEvent\RemoteEvent;

class PmPaymentNotifyEvent extends RemoteEvent
{
    public function __construct(private readonly string $ags, string $txid, private readonly int $status, private readonly string $paymentMethod, private readonly \DateTimeImmutable $createdAt, array $payload)
    {
        parent::__construct('pmPayment.notify', $txid, $payload);
    }

    public function getAgs(): string
    {
        return $this->ags;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function isSuccessful(): bool
    {
        return 1 === $this->getStatus();
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
