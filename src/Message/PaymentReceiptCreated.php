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

/**
 * This message is dispatched when a payment receipt was created.
 */
class PaymentReceiptCreated implements LoggableMessageInterface
{
    public function __construct(private int $paymentId)
    {
    }

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getRelated(): array
    {
        return [
            'Payment' => $this->paymentId,
        ];
    }
}