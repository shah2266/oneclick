<?php

namespace App\Http\Controllers\IOS;

use App\Http\Controllers\Controller;
use App\Traits\ExcelHelper;
use App\Traits\ReportDateHelper;
use Illuminate\Http\Request;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class IosDestinationWiseOutgoingReportController extends Controller
{
    use SQLQueryServices, ExcelHelper, ReportDateHelper;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->initialize('H', 'I', $this->dbSchema(), $this->reportHeading());
    }

    /**
     * @return string[]
     */
    private function reportHeading($fromDate = null, $toDate = null, $direction = null): array
    {
        return [
            'Day wise traffic summary',
            'Report: Destination wise outgoing',
            'Platform: IOS',
            'From Date: ' . Carbon::parse($fromDate)->format('d-M-Y'),
            'To Date: ' . Carbon::parse($toDate)->format('d-M-Y'),
            ($direction == 1) ? 'Direction: Int. Incoming' : 'Direction: Int. Outgoing',
        ];
    }

    /**
     * @return string[]
     */
    private function tableHeading(): array
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
        // Set the day to 1 to get the first day of the month
        $firstDateOfMonth = Carbon::now()->firstOfMonth()->format('Ymd');
        $firstDateOfMonth = '01 May 2024';

        // Get the current date
        $currentDate = Carbon::now()->subDays()->format('Ymd');
        $currentDate = '02 May 2024';

        $this->generateExcel($firstDateOfMonth, $currentDate);
        //echo (env('APP_ENV') !== 'local') ? 'Production' : 'local';
        dd('test');
    }


    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function generateExcel($fromDate, $toDate, $scheduleGenerateType = false): bool
    {
        $direction = 2;  // '1 => Int. Incoming'; '2 => Int. Outgoing'

        // Calculate and set totals
        $columns = ['A', 'I', 'J', 'K'];

        $queryResult = $this->fetchDestinationWiseDataFromIos('CallSummary', 'TrafficDate', $fromDate, $toDate);
        //$result = $this->fetchDestinationWiseDataFromIos('DestinationWiseOutgoingReport', 'traffic_date', $fromDate, $toDate);

        $this->excel->getActiveSheet()->setTitle('Destination_wise_og_report');
        $this->setDataInSpreadsheet($this->excel, 0, $this->reportHeading($fromDate, $toDate, $direction), $this->tableHeading(), $this->dbSchema(), $queryResult, $columns);

        $directory1 = 'ios/schedule/destinationwisereport/ios_des_report '. $this->dateFormat($fromDate).' to '. $this->dateFormat($toDate) .'.xlsx';
        $directory2 = 'ios/destinationwisereport/ios_des_report_'. $this->dateFormat($fromDate).' to '. $this->dateFormat($toDate) .'.xlsx';

        $this->saveFile($this->excel, $scheduleGenerateType, $directory1, $directory2);

        return true;
    }

}
