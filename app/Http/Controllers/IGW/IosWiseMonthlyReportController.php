<?php

namespace App\Http\Controllers\IGW;

use App\Http\Controllers\Controller;
use App\Traits\ExcelHelper;
use App\Traits\ReportDateHelper;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class IosWiseMonthlyReportController extends Controller
{
    use SQLQueryServices, ExcelHelper, ReportDateHelper;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->initialize('B', 'C', $this->dbSchema(), $this->reportHeading());
    }

    /**
     * @return string[]
     */
    private function reportHeading($fromDate = null, $toDate = null, $direction = null): array
    {
        return [
            'IOS wise monthly incoming report',
            'Platform: IGW',
            'From Date: ' . Carbon::parse($fromDate)->format('d-M-Y'),
            'To Date: ' . Carbon::parse($toDate)->format('d-M-Y'),
            ($direction == 1) ? 'Direction: Int. Incoming' : 'Direction: Int. Outgoing',
            'Month: ' . Carbon::parse($fromDate)->format('F - Y')
        ];
    }

    /**
     * @return string[]
     */
    private function tableHeading(): array
    {
        return [
            'Month',
            'Company Name',
            'No of Call',
            'Dur(Min)'
        ];
    }

    /**
     * @return string[]
     */
    private function dbSchema(): array
    {
        return [
            'month_name',
            'company_name',
            'successful_call',
            'duration'
        ];
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index()
    {

//        request()->validate([
//            'fromDate'  => 'required',
//            'toDate'    => 'required',
//        ]);
//
//        $fromDate  = Carbon::parse(request()->fromDate)->format('Ymd');
//        $toDate    = Carbon::parse(request()->toDate)->format('Ymd');

        $this->generateExcel();
        dd('test');
    }


    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function generateExcel($fromDate = null, $toDate = null, $scheduleGenerateType = false): bool
    {

        // Get the first date of the previous month and format it as 'Ymd'
        $fromDate = $fromDate ?? Carbon::now()->subMonth()->firstOfMonth()->format('Ymd');

        // Get the last date of the previous month and format it as 'Ymd'
        $toDate = $toDate ?? Carbon::now()->subMonth()->lastOfMonth()->format('Ymd');

        $direction = 1;  // '1 => Int. Incoming'; '2 => Int. Outgoing'

        // Calculate and set totals
        $columns = ['A', 'C', 'D'];

        $queryResult = $this->fetchIosWiseIncomingFromIgw('CallSummary', 'TrafficDate', $fromDate, $toDate); // table:CDR_MAIN, column:ConnectionTime

        $this->excel->getActiveSheet()->setTitle('IOS wise incoming');
        $this->setDataInSpreadsheet($this->excel, 0, $this->reportHeading($fromDate, $toDate, $direction), $this->tableHeading(), $this->dbSchema(), $queryResult, $columns, false);

        // Get the previous month name and year in the "Month-Year" format
        $previousMonth = Carbon::now()->subMonth()->format('F-Y');

        $directory1 = 'igw/schedule/monthly/ios/BTrac IGW IC '. $previousMonth .'.xlsx';
        $directory2 = 'igw/monthly/ios/BTrac IGW IC '. $previousMonth .'.xlsx';

        $this->saveFile($this->excel, $scheduleGenerateType, $directory1, $directory2);

        return true;
    }
}
