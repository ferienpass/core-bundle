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

use Ferienpass\CoreBundle\Export\Offer\OffersExportInterface;

interface ExcelExportInterface extends OffersExportInterface
{
    /**
     * Return an iterable collection of cells to export into XLSX spreadsheet with
     * the format: 'Column Name' => fn(CellDto $cell)
     * The lambda must return the cell value (arrow function) or set the value on the cell object.
     */
    public function columns(): iterable;
}
