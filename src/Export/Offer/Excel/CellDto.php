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

namespace Ferienpass\CoreBundle\Export\Offer\Excel;

use Ferienpass\CoreBundle\Entity\Offer;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Style\Style;

final class CellDto
{
    private Offer $offer;
    private Cell $cell;
    private Style $style;

    public function __construct(Offer $offer, Cell $cell, Style $style)
    {
        $this->offer = $offer;
        $this->cell = $cell;
        $this->style = $style;
    }

    public function offer(): Offer
    {
        return $this->offer;
    }

    public function cell(): Cell
    {
        return $this->cell;
    }

    public function style(): Style
    {
        return $this->style;
    }

    public function setValue(string $value): void
    {
        $this->cell()->setValue($value);
    }
}
