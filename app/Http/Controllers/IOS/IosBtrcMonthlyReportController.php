<?php

namespace App\Http\Controllers\IOS;

use App\Http\Controllers\Controller;
use App\Models\IofCompany;
use App\Traits\ExcelDataFormatting;
use App\Traits\ExcelHelper;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IosBtrcMonthlyReportController extends Controller
{
    use SQLQueryServices, ExcelDataFormatting, ExcelHelper;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->initialize('C', 'D', $this->dbSchema(), $this->reportHeading());
    }

    /**
     * @return string[]
     */
    private function reportHeading($fromDate = null, $toDate = null, $direction = null): array
    {
        return [
            'Traffic Summary report for BTRC',
            'Platform: IOS',
            'From Date: ' . Carbon::parse($fromDate)->format('d-M-Y'),
            'To Date: ' . Carbon::parse($toDate)->format('d-M-Y'),
            ($direction == 1) ? 'Direction: Int. Incoming' : 'Direction: Int. Outgoing',
            'Month: ' . Carbon::parse($fromDate)->format('F - Y')
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
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index()
    {
        // Get the first date of the previous month and format it as 'Ymd'
        // $firstDateOfPreviousMonth = '20240101';

        // Get the last date of the previous month and format it as 'Ymd'
        // $lastDateOfPreviousMonth = '20240131';

        // $this->generateExcel($firstDateOfPreviousMonth, $lastDateOfPreviousMonth);

        // dump($firstDateOfPreviousMonth . ' ' . $lastDateOfPreviousMonth );

         dump($this->companies());

        dd('test');
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function create()
    {
        request()->validate([
            'fromDate'  => 'required',
            'toDate'    => 'required',
        ]);

        $fromDate  = Carbon::parse(request()->fromDate)->format('Ymd');
        $toDate    = Carbon::parse(request()->toDate)->format('Ymd');

        $this->generateExcel($fromDate, $toDate);

    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateExcel($fromDate = null, $toDate = null, $scheduleGenerateType = false): bool
    {

        // Get the first date of the previous month and format it as 'Ymd'
        $fromDate = $fromDate ?? Carbon::now()->subMonth()->firstOfMonth()->format('Ymd');

        // Get the last date of the previous month and format it as 'Ymd'
        $toDate = $toDate ?? Carbon::now()->subMonth()->lastOfMonth()->format('Ymd');

        // Calculate and set totals
        $columns = ['A', 'D', 'E', 'F'];

        foreach ($this->companies() as $companyId => $companyName) {
            // Reinitialize $this->excel for each iteration
            $this->excel = new Spreadsheet();

            $result = $this->queries($fromDate, $toDate, $companyId);

            $sheetName = $this->workSheetName();
            $hasData = false; // Flag to track if any data is found
            //dump($result);
            for($i = 0; $i < count($result); $i++) {
                $sheetData = Collection::make($result[$i]['data']);
                if(!$sheetData->isEmpty()) {
                    $hasData = true; // Set flag to true if data is found
                    $direction = ($i < 2) ? '1' : '2'; // Direction
                    ($i == 0) ? $this->excel->getActiveSheet()->setTitle($sheetName[$i]) : $this->excel->createSheet()->setTitle($sheetName[$i]);
                    $this->setDataInSpreadsheet($this->excel, $i, $this->reportHeading($fromDate, $toDate, $direction), $this->tableHeading($direction), $this->dbSchema(), $result[$i], $columns, false);
                }
            }

            // If no data is found, skip saving the Excel file
            if (!$hasData) {
                continue;
            }

            // Get the previous month name and year in the "Month-Year" format
            $previousMonth = Carbon::now()->subMonth()->format('F-Y');

            $directory1 = 'ios/schedule/btrcmonthlyreport/icxandanswise/' . $companyName .', '. $previousMonth . '.xlsx';
            $directory2 = 'ios/btrcmonthlyreport/icxandanswise/' . $companyName .', '. $previousMonth . '.xlsx';

            $this->saveFile($this->excel, $scheduleGenerateType, $directory1, $directory2);

        }

        return true;
    }

    /**
     * @return array
     */
    public function companies(): array
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
            3,  // Bangladesh Telecommunications Company Limited
            4,  // Mir Telecom Limited
            5,  // NovoTel Limited
            6,  // Global Voice Telecom Limited
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
     * @param $fromDate
     * @param $toDate
     * @param $companyId
     * @return array
     */
    private function queries($fromDate, $toDate, $companyId): array
    {

        $icxIncoming = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $fromDate, $toDate, 1, $companyId, 'OutCompanyID');
        $ansIncoming = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $fromDate, $toDate, 1, $companyId, 'ANSID');
        $icxOutgoing = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $fromDate, $toDate, 2, $companyId, 'InCompanyID');
        $ansOutgoing = $this->fetchIcxAndAnsData('CallSummary', 'TrafficDate', $fromDate, $toDate, 2, $companyId, 'ANSID');

        return [$icxIncoming, $ansIncoming, $icxOutgoing, $ansOutgoing];
    }

}
