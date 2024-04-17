<?php

namespace App\Http\Controllers\IOS;

use App\Http\Controllers\Controller;
use App\Traits\ExcelHelper;
use Illuminate\Http\Request;
use App\Models\IofCompany;
use App\Traits\ExcelDataFormatting;
use App\Traits\ScheduleProcessing;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IosDestinationWiseOutgoingReportController extends Controller
{
    use SQLQueryServices, ExcelDataFormatting, ExcelHelper, ScheduleProcessing;

    const CELL_NAME = 'A';
    const A_ASCII_VALUE = 65;
    const TABLE_HEADER_CELL = 7;
    const REPORT_FIRST_CELL = 8;
    private $last_report_column;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->last_report_column = count($this->dbSchema()) - 1;
    }

    /**
     * @return string[]
     */
    private function reportHeading($fromDate, $toDate, $direction): array
    {
        return [
            'Traffic Summary',
            'From Date: ' . Carbon::parse($fromDate)->format('d-M-Y'),
            'To Date: ' . Carbon::parse($toDate)->format('d-M-Y'),
            'Direction: ' . $direction
        ];
    }

    /**
     * @return string[]
     */
    private function tableHeading($direction = null): array
    {
        return [
            'Traffic date',
            'ICX name',
            'ICX route name',
            'IGW name',
            'IGW route name',
            'Country',
            'Destination',
            'Destination code',
            'Successful call',
            'Duration',
            'Bill duration'
        ];
    }

    /**
     * @return string[]
     */
    private function dbSchema(): array
    {
        return [
            'traffic_date',
            'icx_name',
            'icx_route_name',
            'igw_name',
            'igw_route_name',
            'country',
            'destination',
            'destination_code',
            'successful_call',
            'duration',
            'bill_duration'
        ];
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index()
    {
        // Get the first date of the previous month and format it as 'Ymd'
        $fromDate = '20240101';

        // Get the last date of the previous month and format it as 'Ymd'
        $toDate = '20240105';

         dump($this->last_report_column);
         $this->generateExcel($fromDate, $toDate);


        dd('test');
    }


    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function generateExcel($fromDate, $toDate, $scheduleGenerateType = false): bool
    {

        $result = $this->fetchDestinationWiseDataFromIos('CallSummary', 'TrafficDate', $fromDate, $toDate);

        $this->excel->getActiveSheet()->setTitle('Destination_wise_og_report');
        $this->setDataInSpreadsheet(0, $fromDate, $toDate, 2 , $result);

        //Authors
        $this->authors($this->excel);

        $writer = new Xlsx($this->excel);

        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/ios/schedule/destinationwisereport/destination_wise_og_report_from_ios.xlsx');

        } else {
            $writer->save(public_path().'/platform/ios/destinationwisereport/destination_wise_og_report_from_ios.xlsx');
        }

        return true;
    }

    /**
     * Sets report and table headings.
     *
     * @param $excelInstance
     * @param $fromDate
     * @param $toDate
     * @param $direction
     */
    private function setReportAndTableHeadings($excelInstance, $fromDate, $toDate, $direction)
    {
        $dir = ($direction == 1) ? 'Int. Incoming' : 'Int. Outgoing';

        // Set report heading
        $this->setReportHeading($excelInstance, self::CELL_NAME, (self::A_ASCII_VALUE + $this->last_report_column), $this->reportHeading($fromDate, $toDate, $dir));

        // Set table heading
        $this->setTableHeading($excelInstance, self::A_ASCII_VALUE, (self::A_ASCII_VALUE + $this->last_report_column), self::TABLE_HEADER_CELL, $this->tableHeading());
    }

    /**
     * @throws Exception
     */
    private function setDataInSpreadsheet($activeSheet, $fromDate, $toDate, $direction, $queryResult): Spreadsheet
    {
        $a_ascii_value = self::A_ASCII_VALUE;
        $report_first_cell = self::REPORT_FIRST_CELL;
        $tbl_header_cell = self::TABLE_HEADER_CELL;

        // Set the active sheet index
        $this->activeSheet($this->excel, $activeSheet);

        // Set report and table headings
        $this->setReportAndTableHeadings($this->excel, $fromDate, $toDate, $direction);

        // Populate data from query result
        $this->populateData($this->excel, $a_ascii_value, $report_first_cell, $this->dbSchema(), $queryResult['data']);

        // Calculate and set totals
        $columns = ['A', 'I', 'J', 'K'];
        $lastCell = $this->calculateAndSetTotals($this->excel,$tbl_header_cell, $report_first_cell, $columns , $queryResult['total_count']);

        // Format the spreadsheet
        $this->formatSpreadsheet($this->excel, $a_ascii_value, ($a_ascii_value + $this->last_report_column), $tbl_header_cell, $report_first_cell, $lastCell, 'H', 'I');

        // Set default active sheet
        $this->excel->setActiveSheetIndex(0);

        // Return the spreadsheet object
        return $this->excel;
    }
}
