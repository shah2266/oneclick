<?php

namespace App\Http\Controllers\IOS;

use App\Http\Controllers\Controller;
use App\Models\IofCompany;
use App\Traits\ExcelDataFormatting;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Authors\AuthorInformation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IosBtrcMonthlyReportController extends Controller
{
    use SQLQueryServices, ExcelDataFormatting;

    const CELL_NAME = 'A';
    const A_ASCII_VALUE = 65;
    const TABLE_HEADER_CELL = 7;
    const REPORT_FIRST_CELL = 8;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
    }

    /**
     * @return string[]
     */
    private function reportHeading($startDate, $endDate, $direction): array
    {
        return [
            'Traffic Summary',
            'From Date: ' . $startDate,
            'To Date: ' . $endDate,
            'Direction: ' . $direction,
            'Month: ' . Carbon::parse($startDate)->format('F Y')
        ];
    }

    /**
     * @return string[]
     */
    private function tableHeading($direction): array
    {
        return ($direction == 1) ?
            ['Month', 'In company', 'Out Company', 'No of Call', 'Dur (Min)', 'Bill Dur (Min)'] :
            ['Month', 'In Company', 'Out Company', 'No of Call', 'Dur (Min)', 'Bill Dur (Min)'];
    }

    /**
     * @return string[]
     */
    private function dbSchema(): array
    {
        return [
            'month',
            'inCompany',
            'outCompany',
            'successfulCall',
            'duration',
            'billDuration'
        ];
    }

    /**
     * @return string[]
     */
    private function workSheetName(): array
    {
        return [
            'incoming-icx-wise',
            'incoming-ans-wise',
            'outgoing-icx-wise',
            'outgoing-ans-wise'
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function index()
    {
        $startDate = '20240301';
        $endDate = '20240331';

        foreach ($this->companies() as $companyId => $companyName) {
            // Reinitialize $this->excel for each iteration
            $this->excel = new Spreadsheet();

            $result = $this->queries($startDate, $endDate, $companyId);

            $sheetName = $this->workSheetName();
            $hasData = false; // Flag to track if any data is found
            //dump($result);
            for($i = 0; $i < count($result); $i++) {
                $sheetData = Collection::make($result[$i]['data']);
                if(!$sheetData->isEmpty()) {
                    $hasData = true; // Set flag to true if data is found
                    $direction = ($i < 2) ? '1' : '2'; // Direction
                    ($i == 0) ? $this->excel->getActiveSheet()->setTitle($sheetName[$i]) : $this->excel->createSheet()->setTitle($sheetName[$i]);
                    $this->setDataInSpreadsheet($i, $startDate, $endDate, $direction , $result[$i]);
                }
            }

            // If no data is found, skip saving the Excel file
            if (!$hasData) {
                continue;
            }
            //Authors
            $this->authors($this->excel);

            // Get the previous month name and year in the "Month-Year" format
            $previousMonth = Carbon::now()->subMonth()->format('F-Y');
            $writer = new Xlsx($this->excel);
            $writer->save(public_path().'/platform/ios/btrcmonthlyreport/icxandanswise/' . $companyName .', '. $previousMonth . '.xlsx');
        }
        dd('test');
    }

    /**
     * @return array
     */
    private function companies(): array
    {

        // Retrieve data from the database
        $companies = IofCompany::where('type', 1)
            ->select('systemId', 'shortName')
            ->get();

        // Initialize an empty associative array
        $companyDetails = [];

        // Add the systemId values to be ignored
        $ignoredCompanyIds =  [
            2,  // Bangla Trac Communications Limited
            4,  // Mir Telecom Limited
            5,  // NovoTel Limited
            //6,  // Global Voice Telecom Limited
            //7,  // BG Tel Limited
            //8,  // HRC Technologies Limited
            //9,  // Roots Communication Limited
            10,  // 1Asia Alliance Gateway Limited
            //11,  // Unique Infoway Limited
            12,  // Sigma Telecom Limited
            //14,  // DBL Telecom Limited
            //16,  // First Communication Limited
            //19,  // MOS5 Tel Limited
            20,  // Cel Telecom Limited
            21,  // Ranks Telecom Limited
            22,  // Bangla Tel Limited
            //23,  // SM Communication Limited
            24,  // Platinum Communications Limited
            26,  // Bangladesh International Gateway Limited
            //27,  // Digicon Telecommunication Limited
            28,  // Venus Telecom Limited
            //30,  // Songbird Telecom Limited
            118, // LR Telecom Limited
        ];


        foreach ($companies as $company) {

            // Ignore specific systemId values
            if (in_array($company->systemId, $ignoredCompanyIds)) {
                continue; // Skip this company
            }

            $companyDetails[$company->systemId] = $company->shortName;
        }

        // Sorting ASC
        ksort($companyDetails);

        return $companyDetails;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $companyId
     * @return array
     */
    private function queries($startDate, $endDate, $companyId): array
    {

        $icxIncoming = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $startDate, $endDate, 1, $companyId, 'OutCompanyID');
        $ansIncoming = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $startDate, $endDate, 1, $companyId, 'ANSID');
        $icxOutgoing = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $startDate, $endDate, 2, $companyId, 'InCompanyID');
        $ansOutgoing = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $startDate, $endDate, 2, $companyId, 'ANSID');

        return [$icxIncoming, $ansIncoming, $icxOutgoing, $ansOutgoing];
    }

    private function setReportHeading($heading)
    {
        foreach ($heading as $key => $value) {
            $startCoordinate = self::CELL_NAME . ($key + 1);
            $endCoordinate = chr(self::A_ASCII_VALUE + 5) . ($key + 1);
            $this->excel->getActiveSheet()->setCellValue($startCoordinate, $value);
            $this->cellMerge($this->excel, $startCoordinate, $endCoordinate);
            $this->fontBold($this->excel, $startCoordinate, $endCoordinate);
        }
    }

    private function setTableHeading($tableHeading)
    {
        foreach ($tableHeading as $key => $heading) {
            $startCoordinate = chr(self::A_ASCII_VALUE + $key) . self::TABLE_HEADER_CELL;
            $endCoordinate = chr(self::A_ASCII_VALUE + 5) . self::TABLE_HEADER_CELL; // 1 for wrapping total section
            $this->excel->getActiveSheet()->setCellValue($startCoordinate, $heading);
            $this->fontBold($this->excel, $startCoordinate, $endCoordinate);
        }
    }

    /**
     * Sets data in the spreadsheet.
     *
     * @param $activeSheet
     * @param $startDate
     * @param $endDate
     * @param $direction
     * @param $queryResult
     * @return Spreadsheet
     * @throws Exception
     */
    private function setDataInSpreadsheet($activeSheet, $startDate, $endDate, $direction, $queryResult): Spreadsheet
    {
        // Set the active sheet index
        $this->excel->setActiveSheetIndex($activeSheet);

        // Set report and table headings
        $this->setReportAndTableHeadings($startDate, $endDate, $direction);

        // Populate data from query result
        $this->populateData($queryResult);

        // Calculate and set totals
        $lastCell = $this->calculateAndSetTotals($queryResult);

        // Format the spreadsheet
        $this->formatSpreadsheet($lastCell);

        // Set default active sheet
        $this->excel->setActiveSheetIndex(0);

        // Return the spreadsheet object
        return $this->excel;
    }

    /**
     * Sets report and table headings.
     *
     * @param $startDate
     * @param $endDate
     * @param $direction
     */
    private function setReportAndTableHeadings($startDate, $endDate, $direction)
    {
        $dir = ($direction == 1) ? 'Int. Incoming' : 'Int. Outgoing';

        // Set report heading
        $this->setReportHeading($this->reportHeading($startDate, $endDate, $dir));

        // Set table heading
        $this->setTableHeading($this->tableHeading($direction));
    }

    /**
     * Populates data from query result.
     *
     * @param array $queryResult
     */
    private function populateData(array $queryResult)
    {
        // Get database schema
        $schema = $this->dbSchema();
        $totalSchema = count($schema);

        // Populate data row by row
        foreach ($queryResult['data'] as $key => $data) {
            for ($i = 0; $i < $totalSchema; $i++) {
                $fieldName = (string) $schema[$i];
                $cellCoordinate = chr(self::A_ASCII_VALUE + $i) . (self::REPORT_FIRST_CELL + $key);
                $this->excel->getActiveSheet()->setCellValue($cellCoordinate, $data->$fieldName);
            }
        }
    }

    /**
     * Calculates and sets totals.
     *
     * @param $queryResult
     * @return int
     */
    private function calculateAndSetTotals($queryResult): int
    {
        // Calculate total cells
        $beforeLastCell = self::TABLE_HEADER_CELL + $queryResult['total_count'];
        $lastCell = $beforeLastCell + 1;

        // Calculate and set formulas for totals
        $columnsToSum = ['A', 'D', 'E', 'F'];
        foreach ($columnsToSum as $key => $column) {
            $range = $column . self::REPORT_FIRST_CELL . ':' . $column . $beforeLastCell;
            if($key == 0) {
                $this->excel->getActiveSheet()->setCellValue($column . $lastCell, 'Total');
            } else {
                $this->excel->getActiveSheet()->setCellValue($column . $lastCell, '=SUBTOTAL(9,' . $range . ')'); // 9 is sum
            }
        }

        return $lastCell;
    }

    /**
     * Formats the spreadsheet.
     *
     * @param $lastCell
     */
    private function formatSpreadsheet($lastCell)
    {
        // Calculate column coordinates
        $cell_a = chr(self::A_ASCII_VALUE); // Start column
        $cell_f = chr(self::A_ASCII_VALUE + 5); // End column

        // Autoresize columns
        $this->columnAutoresize($this->excel, $cell_a, $cell_f);

        // Merge cells and apply formatting
        $this->cellMerge($this->excel, $cell_a . $lastCell, 'C' . $lastCell); // Merge cells from A to C of the last row
        $this->fontBold($this->excel, $cell_a . $lastCell, $cell_f . $lastCell); // Bold font for cells from A to F of the last row
        $this->formatNumber($this->excel, 'D' . self::REPORT_FIRST_CELL, $cell_f . $lastCell, 1); // Format numbers in cells from D to F starting from the first row of data
        $this->allBorders($this->excel, $cell_a . self::TABLE_HEADER_CELL, $cell_f . $lastCell); // Apply borders to the entire table area

    }

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
