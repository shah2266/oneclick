<?php

namespace App\Traits;

use PhpOffice\PhpSpreadsheet\Style\Border;

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


//        //Table Heading Style
//        $tableHeading = [
//            'font' => [
//                'bold' => true,
//                'size' => 10,
//                'name'  => 'Arial',
//            ],
//            'borders' => [
//                'allBorders' => [
//                    'borderStyle' => Border::BORDER_THIN,
//                ]
//            ],
//        ];
//
//        //All Borders
//        $allBorders = [
//            'font' => [
//                'size' => 10,
//                'name'  => 'Arial',
//            ],
//            'borders' => [
//                'allBorders' => [
//                    'borderStyle' => Border::BORDER_THIN,
//                ],
//            ],
//        ];
    }



}
