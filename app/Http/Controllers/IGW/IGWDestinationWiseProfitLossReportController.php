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

class IGWDestinationWiseProfitLossReportController extends Controller
{
    use SQLQueryServices, ExcelHelper, ReportDateHelper;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->initialize('F', 'G', $this->dbSchema(), $this->reportHeading());
    }

    /**
     * @return string[]
     */
    private function reportHeading($fromDate = null, $toDate = null, $direction = null): array
    {
        return [
            'Day wise profit loss report',
            'Platform: IGW',
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
            'Traffic Date',
            'OS Name',
            'Country',
            'Destination',
            'BTRC Zone Code',
            'OS Zone Code',
            'No of  Call ',
            'Dur(Min)',
            'Bill Dur(Min)',
            'In Rate',
            'Out Rate',
            'BTRC Y Amount($)',
            'OS Y Amount($)',
            'BTRC Y Bill Amount($)',
            'BTRC X Amount(BDT)',
            'Exchange Rate',
            '(BTRC-OS) Y Amount($)',
            'BTRC Y BillAmount(BDT)',
            'Z=X-Y in BDT',
            'Invoice Amount(BDT)',
            'BTRC Part(Z*15%*40%)',
            'OS Y Amount',
            'Actual  Amount(BDT)'
        ];
    }

    /**
     * @return string[]
     */
    private function dbSchema(): array
    {
        return [
            'traffic_date',
            'os_name',
            'country',
            'destination',
            'btrc_zone_code',
            'os_zone_code',
            'successful_call',
            'duration',
            'bill_duration',
            'in_rate',
            'out_rate',
            'btrc_y_amount_in_dollar',
            'os_y_amount_in_dollar',
            'btrc_y_bill_amount_in_dollar',
            'btrc_x_amount_in_bdt',
            'exchange_rate',
            'btrc_os_y_amount_in_dollar',
            'btrc_y_bill_amount_in_bdt',
            'z_amount_in_bdt',
            'invoice_amount_in_bdt',
            'btrc_part',
            'os_y_amount_in_bdt',
            'actual_amount_in_bdt'
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
        //$firstDateOfMonth = '01 May 2024';

        // Get the current date
        $currentDate = Carbon::now()->subDays()->format('Ymd');
        //$currentDate = '02 May 2024';

        $this->dayWiseProfitLoss();
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
        $columns = ['A', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'Q', 'R', 'S', 'T', 'U', 'V', 'W'];

        $queryResult = $this->fetchDestinationWiseProfitLossFromIgw('CallSummary', 'TrafficDate', $fromDate, $toDate);

        $this->excel->getActiveSheet()->setTitle('Outgoing_profit_loss');
        $this->setDataInSpreadsheet($this->excel, 0, $this->reportHeading($fromDate, $toDate, $direction), $this->tableHeading(), $this->dbSchema(), $queryResult, $columns);

        $directory1 = 'igw/schedule/profit_loss/daily/profit_loss_'. $this->dateFormat($fromDate).' to '. $this->dateFormat($toDate) .'.xlsx';
        $directory2 = 'igw/profit_loss/daily/profit_loss_'. $this->dateFormat($fromDate).' to '. $this->dateFormat($toDate) .'.xlsx';

        $this->saveFile($this->excel, $scheduleGenerateType, $directory1, $directory2);

        return true;
    }

    /**
     * @return array
     */
    public function dayWiseProfitLoss(): array
    {

        $dateArray = [$this->getDatesForCurrentMonth(), $this->getDatesForSubMonth()];

        //dump($dateArray);
        $table = "<table border='1' style='border-collapse: collapse; text-align: center'>";
        $table .= "<tr>";
        foreach ($dateArray as $key => $date) {
            list($fromDate, $toDate) = $date;
            $result = $this->fetchDayWiseProfitLoss('CallSummary', 'TrafficDate', $fromDate, $toDate);
            $table .= "<td>" . $this->dataRender($result, $this->dateFormat($toDate, 'F-Y')) . "</td>";
        }
        $table .= "</tr></table>";

        echo $table;
//        dd('end');


        return [
            'dayWise' => $table
        ];

    }


    /**
     * @param $result
     * @param $month
     * @return string
     */
    protected function dataRender($result, $month): string
    {
        $tbl_heading = ['Traffic date', 'Successful call', 'Duration', 'Bill duration', 'Actual amount (BDT)'];
        $schema = ['traffic_date', 'successful_call', 'duration', 'bill_duration', 'actual_amount_bdt'];

        $table = "<table border='1' style='border-collapse: collapse; font-size: 13px; padding: 5px; text-align: center'>";

        $table .= "<tr style='height: 30px; font-size: 15px;'><th colspan='5'>Day wise outgoing profit-loss summary of " . $month . "</th></tr>";

        $table .= "<tr>";
        for($i = 0; $i < count($tbl_heading); $i++) {
            $table .= "<th style='padding: 5px 8px;'>" . $tbl_heading[$i] . "</th>";
        }
        $table .= "</tr>";

        foreach($result['data'] as $data) {
            $table .= "<tr>";
            for($i = 0; $i < count($schema); $i++) {
                $schema_name = $schema[$i];
                $table .= ($i == 0) ? "<td style='padding: 5px 8px;'>" . $data->$schema_name . "</td>"
                    : "<td style='padding: 5px 8px; text-align: right;'>" . number_format($data->$schema_name, 2,) . "</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</table>";

        return $table;
    }

}
