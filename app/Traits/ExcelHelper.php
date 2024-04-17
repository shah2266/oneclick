<?php

namespace App\Traits;

use App\Authors\AuthorInformation;

trait ExcelHelper
{
    use ExcelDataFormatting;

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
     * @param $schemas
     * @param array $queryResult
     */
    protected function populateData($excelInstance, $start_cell, $report_first_cell, $schemas, array $queryResult)
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
