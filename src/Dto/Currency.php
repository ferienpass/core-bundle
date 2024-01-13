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

namespace Ferienpass\CoreBundle\Dto;

class Currency
{
    private readonly int $amount;
    private readonly int $divisor;

    public function __construct(int $amount, int $divisor = 100)
    {
        $this->amount = $amount;
        $this->divisor = $divisor;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDivisor(): int
    {
        return $this->divisor;
    }
}
