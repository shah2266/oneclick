<?php

namespace App\Traits;

use App\Authors\AuthorInformation;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait ExcelHelper
{
    use ExcelDataFormatting;

    private $chunk_size = 20000;
    private $cell_name = 'A';
    private $a_ascii_value = 65;
    private $tbl_header_cell;
    private $report_first_cell;
    private $last_report_column;
    private $merge_to_cell;
    private $format_from_cell;

    /**
     * Get active sheet.
     * @param $merge_to_cell
     * @param $format_from_cell
     * @param array $dbSchema
     * @param array $reportHeading
     */
    public function initialize($merge_to_cell, $format_from_cell, array $dbSchema, array $reportHeading)
    {
        $this->merge_to_cell = $merge_to_cell;
        $this->format_from_cell = $format_from_cell;
        $this->last_report_column = count($dbSchema) - 1;
        $this->tbl_header_cell = count($reportHeading) + 2;
        $this->report_first_cell = $this->tbl_header_cell + 1;
    }

    /**
     * Get active sheet.
     * @param $excelInstance
     * @param $activeSheet
     */
    protected function activeSheet($excelInstance, $activeSheet)
    {
        // Set the active sheet index
        $excelInstance->setActiveSheetIndex($activeSheet);
    }

    /**
     * Sets report headings.
     * @param $excelInstance
     * @param $start_cell
     * @param $end_cell
     * @param $heading
     */
    protected function setReportHeading($excelInstance, $start_cell, $end_cell, $heading)
    {
        foreach ($heading as $key => $value) {
            $startCoordinate = $start_cell . ($key + 1);
            $endCoordinate = chr($end_cell) . ($key + 1);

            $excelInstance->getActiveSheet()->setCellValue($startCoordinate, $value);
            $this->cellMerge($excelInstance, $startCoordinate, $endCoordinate);
            $this->fontBold($excelInstance, $startCoordinate, $endCoordinate);
        }
    }

    /**
     * Sets report and table headings.
     * @param $excelInstance
     * @param $start_cell
     * @param $end_cell
     * @param $tbl_header_cell
     * @param $tableHeading
     */
    protected function setTableHeading($excelInstance, $start_cell, $end_cell, $tbl_header_cell, $tableHeading)
    {
        foreach ($tableHeading as $key => $heading) {
            $startCoordinate = chr($start_cell + $key) . $tbl_header_cell;
            $endCoordinate = chr($end_cell) . $tbl_header_cell; // 1 for wrapping total section
            $excelInstance->getActiveSheet()->setCellValue($startCoordinate, $heading);
            $this->fontBold($excelInstance, $startCoordinate, $endCoordinate);
        }
    }

    /**
     * Populates data from query result.
     * @param $excelInstance
     * @param $start_cell
     * @param $report_first_cell
     * @param array $schemas
     * @param array $queryResult
     */
    protected function populateData($excelInstance, $start_cell, $report_first_cell, array $schemas, array $queryResult)
    {
        // Get database schema
        $schema = $schemas;
        $totalSchema = count($schemas);

        // Populate data row by row
        foreach ($queryResult as $key => $data) {
            for ($i = 0; $i < $totalSchema; $i++) {
                $fieldName = (string) $schema[$i];
                $cellCoordinate = chr($start_cell + $i) . ($report_first_cell + $key);
                $excelInstance->getActiveSheet()->setCellValue($cellCoordinate, $data->$fieldName);
            }
        }
    }


    /**
     * Calculates and sets totals.
     * @param $excelInstance
     * @param $tbl_header_cell
     * @param $report_first_cell
     * @param $columns
     * @param $total_row
     * @return int
     */
    protected function calculateAndSetTotals($excelInstance, $tbl_header_cell, $report_first_cell, $columns, $total_row): int
    {
        // Calculate total cells
        $beforeLastCell = $tbl_header_cell + $total_row;
        $lastCell = $beforeLastCell + 1;

        // Calculate and set formulas for totals
        foreach ($columns as $key => $column) {
            $range = $column . $report_first_cell . ':' . $column . $beforeLastCell;
            if($key == 0) {
                $excelInstance->getActiveSheet()->setCellValue($column . $lastCell, 'Total');
            } else {
                $excelInstance->getActiveSheet()->setCellValue($column . $lastCell, '=SUBTOTAL(9,' . $range . ')'); // 9 is sum
            }
        }

        return $lastCell;
    }

    /**
     * Formats the spreadsheet.
     * @param $excelInstance
     * @param $start_cell
     * @param $end_cell
     * @param $tbl_header_cell
     * @param $report_first_cell
     * @param $lastCell
     * @param $merge_to_cell
     * @param $format_from_cell
     */
    protected function formatSpreadsheet($excelInstance, $start_cell, $end_cell, $tbl_header_cell, $report_first_cell, $lastCell, $merge_to_cell, $format_from_cell)
    {
        // Calculate column coordinates
        $first_cell_name = chr($start_cell); // Start column
        $last_cell_name = chr($end_cell); // End column

        // Autoresize columns
        $this->columnAutoresize($excelInstance, $first_cell_name, $last_cell_name);

        // Merge cells and apply formatting
        $this->cellMerge($excelInstance, $first_cell_name . $lastCell, $merge_to_cell . $lastCell); // Merge cells from A to C of the last row
        $this->fontBold($excelInstance, $first_cell_name . $lastCell, $last_cell_name . $lastCell); // Bold font for cells from A to F of the last row
        $this->formatNumber($excelInstance, $format_from_cell . $report_first_cell, $last_cell_name . $lastCell, 1); // Format numbers in cells from D to F starting from the first row of data
        $this->allBorders($excelInstance, $first_cell_name . $tbl_header_cell, $last_cell_name . $lastCell); // Apply borders to the entire table area
    }


    //    /**
//     * Sets report and table headings.
//     *
//     * @param $excelInstance
//     * @param $fromDate
//     * @param $toDate
//     * @param $direction
//     */
//    private function setReportAndTableHeadings($excelInstance, $fromDate, $toDate, $direction)
//    {
//        $dir = ($direction == 1) ? 'Int. Incoming' : 'Int. Outgoing';
//
//        // Set report heading
//        $this->setReportHeading($excelInstance, $this->cell_name, ($this->a_ascii_value + $this->last_report_column), $this->reportHeading($fromDate, $toDate, $dir));
//
//        // Set table heading
//        $this->setTableHeading($excelInstance, $this->a_ascii_value, ($this->a_ascii_value + $this->last_report_column), $this->tbl_header_cell, $this->tableHeading());
//    }


    /**
     * Sets report headings.
     * @param $excelInstance
     * @param $activeSheet
     * @param $reportHeading
     * @param $tableHeading
     * @param $dbSchema
     * @param $queryResult
     * @param $totalColumns
     * @param bool $chunkDataProcess
     * @return mixed
     */
    protected function setDataInSpreadsheet($excelInstance, $activeSheet, $reportHeading, $tableHeading, $dbSchema, $queryResult, $totalColumns, bool $chunkDataProcess = true)
    {

        // Set the active sheet index
        $this->activeSheet($excelInstance, $activeSheet);

        // Set report and table headings
        // $this->setReportAndTableHeadings($excelInstance, $fromDate, $toDate, $direction);

        // Set report heading
        $this->setReportHeading($excelInstance, $this->cell_name, ($this->a_ascii_value + $this->last_report_column), $reportHeading);

        // Set table heading
        $this->setTableHeading($excelInstance, $this->a_ascii_value, ($this->a_ascii_value + $this->last_report_column), $this->tbl_header_cell, $tableHeading);

        if($chunkDataProcess) {
            // Split data into chunks and write to Excel
            $startIndex = 0;

            foreach (array_chunk($queryResult['data'], $this->chunk_size) as $chunk) {
                set_time_limit(600);

                // Populate data from query result
                $this->populateData($excelInstance, $this->a_ascii_value, $this->report_first_cell + $startIndex, $dbSchema, $chunk);

                // Update the starting index for the next chunk
                $startIndex += count($chunk);
            }

        } else {
            // Populate data from query result
            $this->populateData($excelInstance, $this->a_ascii_value, $this->report_first_cell, $dbSchema, $queryResult['data']);
        }

        $lastCell = $this->calculateAndSetTotals($excelInstance,$this->tbl_header_cell, $this->report_first_cell, $totalColumns , $queryResult['total_count']);

        // Format the spreadsheet
        $this->formatSpreadsheet($excelInstance, $this->a_ascii_value, ($this->a_ascii_value + $this->last_report_column), $this->tbl_header_cell, $this->report_first_cell, $lastCell, $this->merge_to_cell, $this->format_from_cell);

        // Set default active sheet
        $excelInstance->setActiveSheetIndex(0);

        // Return the spreadsheet object
        return $excelInstance;
    }

    /**
     * @throws Exception
     */
    protected function saveFile($excelInstance, $scheduleGenerateType, $directory1, $directory2)
    {
        //Authors
        $this->authors($excelInstance);

        $writer = new Xlsx($excelInstance);

        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/' . $directory1);
        } else {
            $writer->save(public_path().'/platform/' . $directory2);
        }
    }

    /**
     * Authors.
     * @param $excelInstance
     */
    protected function authors($excelInstance)
    {
        //Creator Information
        $authorsInfo = AuthorInformation::authors();
        $excelInstance->getProperties()
            ->setCreator($authorsInfo['creator'])
            ->setLastModifiedBy($authorsInfo['creator'])
            ->setTitle($authorsInfo['sTitle'])
            ->setSubject($authorsInfo['sSubject'])
            ->setDescription($authorsInfo['sDescription'])
            ->setKeywords($authorsInfo['sKeywords'])
            ->setCategory($authorsInfo['sCategory']);
    }
}
