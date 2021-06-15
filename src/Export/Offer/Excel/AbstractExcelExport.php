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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractExcelExport implements ExcelExportInterface
{
    public function generate(iterable $offers, string $destination = null): string
    {
        $tmpPath = sys_get_temp_dir().'/xlsx';
        $xlsxPath = sprintf('%s/%s.xlsx', $tmpPath, random_int(0, 10000));

        $filesystem = new Filesystem();
        $filesystem->mkdir($tmpPath);

        $writer = new XlsxWriter($this->createSpreadsheet($offers));
        $writer->save($xlsxPath);

        if (null !== $destination) {
            $filesystem->copy($xlsxPath, $destination);
        }

        return $destination ?? $xlsxPath;
    }

    protected function createSpreadsheet(iterable $offers): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $sheet = $spreadsheet->getActiveSheet();

        $cols = $this->columns();
        $cols = $cols instanceof \Traversable ? iterator_to_array($cols, true) : $cols;

        // Header row
        $iCol = 1;
        foreach (array_keys($cols) as $colName) {
            $sheet->getStyleByColumnAndRow($iCol, 1)->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow($iCol, 1, $colName);

            ++$iCol;
        }

        // Content rows
        $iRow = 2;
        foreach ($offers as $offer) {
            $iCol = 1;

            foreach ($cols as $col) {
                $cell = $sheet->getCellByColumnAndRow($iCol, $iRow);
                if (null === $cell) {
                    continue;
                }

                $style = $sheet->getStyleByColumnAndRow($iCol, $iRow);
                $dto = new CellDto($offer, $cell, $style);

                // If the callback has a return value (e.g. for arrow functions), set the value
                if ($val = $col($dto)) {
                    $cell->setValue($val);
                }

                ++$iCol;
            }

            ++$iRow;
        }

        return $spreadsheet;
    }
}
