<?php

namespace App\Traits;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

trait ExcelDataFormatting
{

    public function columnAutoresize($excelInstance, $start, $end)
    {
        foreach(range($start, $end) as $index) {
            $excelInstance->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }
    }

    public function cellMerge($excelInstance, $start, $end)
    {
        $cells = $start . ':' . $end;
        $excelInstance->getActiveSheet()->mergeCells($cells);
    }

    public function fontBold($excelInstance, $start, $end)
    {
        $cells = $start . ':' . $end;

        $excelInstance->getActiveSheet()->getStyle($cells)->applyFromArray(
            [
                'font' => [
                    'bold' => true,
                ]
            ]
        );
    }

    public function allBorders($excelInstance, $start, $end)
    {
        $cells = $start . ':' . $end;

        $excelInstance->getActiveSheet()->getStyle($cells)->applyFromArray(
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]
        );

    }

    public function formatNumber($excelInstance, $start, $end, $format)
    {
        $cells = $start . ':' . $end;
        $formatCode = $this->getFormatCode($format);
        $excelInstance->getActiveSheet()->getStyle($cells)->getNumberFormat()->setFormatCode($formatCode);
    }

    private function getFormatCode($format): string
    {
        switch ($format) {
            case 1:
                return NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
            case 2:
                return NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2;
            case 3:
                return NumberFormat::FORMAT_DATE_YYYYMMDD;
            default:
                return ''; // Default
        }
    }

}
