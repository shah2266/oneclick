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
        //$currentDate = Carbon::now()->subDays()->format('Ymd');
        //$currentDate = '02 May 2024';


        $data = $this->dayWiseProfitLoss();
        //dump($data['totalProfit']);
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
        //$dateArray = [$this->getDatesForCurrentMonth(), $this->getDatesForSubMonth()];

        list($fromDate, $toDate) = $this->getDatesForCurrentMonth();
        list($subFromDate, $subToDate) = $this->getDatesForSubMonth();

        $table = "<table border='1' style='border-collapse: collapse; text-align: center'>";
        $table .= "<tr>";

        // Fetch data for both current and previous month
        $currentMonthResult = $this->fetchDayWiseProfitLoss('CallSummary', 'TrafficDate', $fromDate, $toDate);
        $previousMonthResult = $this->fetchDayWiseProfitLoss('CallSummary', 'TrafficDate', $subFromDate, $subToDate);

        // Render the table with the data
        $table .= "<td>" . $this->dataRender($currentMonthResult, $previousMonthResult, $this->dateFormat($toDate, 'F-Y'), $this->dateFormat($subToDate, 'F-Y')) . "</td>";
        $table .= "</tr>";
        $table .= "</table>";

        return [
            'totalProfit' => $this->currentMonthProjection($currentMonthResult['data']),
            'dayWise' => $table
        ];
    }

    /**
     * @param $currentMonthResult
     * @param $previousMonthData
     * @param $current_month
     * @param $previous_month
     * @return string
     */
    protected function dataRender($currentMonthResult, $previousMonthData, $current_month, $previous_month): string
    {
        $tbl_heading = [
            'Date', 'Calls', 'Dur', 'Bill.Dur', 'Amount (BDT)',
            'Diff amount',
            'Date', 'Calls', 'Dur', 'Bill.Dur', 'Amount (BDT)'
        ];
        $schema = ['traffic_date', 'successful_call', 'duration', 'bill_duration', 'actual_amount_bdt'];

        $totals = [
            'current' => array_fill_keys($schema, 0),
            'previous' => array_fill_keys($schema, 0),
            'diff' => 0
        ];

        $daysInCurrentMonth = count($currentMonthResult['data']);
        $daysInPreviousMonth = count($previousMonthData['data']);
        $maxDays = max($daysInCurrentMonth, $daysInPreviousMonth);

        $table = "<table border='1' style='border-collapse: collapse; font-size: 12px; padding: 5px; text-align: center'>";

        $table .= "<tr style='height: 30px; font-size: 12px; background: #effeff;'>";
        $table .= "<th colspan='5'>OG Profit-loss summary of " . $current_month . "</th>";
        $table .= "<th style='padding: 5px;'>Diff. Amount <br>" . $current_month . ' - ' . $previous_month . "</th>";
        $table .= "<th colspan='5'>OG Profit-loss summary of " . $previous_month . "</th>";
        $table .= "</tr>";

        $table .= $this->renderTableHeader($tbl_heading);

        for ($index = 0; $index < $maxDays; $index++) {
            $currentData = $index < $daysInCurrentMonth ? $currentMonthResult['data'][$index] : null;
            $previousData = $index < $daysInPreviousMonth ? $previousMonthData['data'][$index] : null;
            $table .= $this->renderTableRow($currentData, $previousData, $schema, $totals);
        }

        $table .= $this->renderTotalRow($schema, $totals);

        $table .= "</table>";

        return $table;
    }

    /**
     * @param array $headings
     * @return string
     */
    protected function renderTableHeader(array $headings): string
    {
        $header = "<tr>";
        foreach ($headings as $heading) {
            $header .= "<th style='padding: 5px 8px; background: #a8d7bd;'>" . $heading . "</th>";
        }
        $header .= "</tr>";

        return $header;
    }

    /**
     * @param $currentData
     * @param $previousData
     * @param array $schema
     * @param array $totals
     * @return string
     */
    protected function renderTableRow($currentData, $previousData, array $schema, array &$totals): string
    {
        $row = "<tr>";

        $row .= $this->renderDataCells($currentData, $schema, $totals['current']);
        $row .= $this->renderDiffCell($currentData, $previousData, $totals['diff']);
        $row .= $this->renderDataCells($previousData, $schema, $totals['previous']);

        $row .= "</tr>";

        return $row;
    }

    /**
     * @param $data
     * @param array $schema
     * @param array $totals
     * @return string
     */
    protected function renderDataCells($data, array $schema, array &$totals): string
    {
        $cells = "";

        if ($data) {
            foreach ($schema as $field) {
                if ($field != 'traffic_date') {
                    $totals[$field] += $data->$field;
                }

                $style = "style='padding: 5px 8px; text-align: right;'";

                if($field === 'traffic_date') {
                    $cells .= "<td style='padding: 5px 8px;'>" . $data->$field . "</td>";
                } else {
                    $cells .= ($field === 'successful_call') ? "<td $style>" . number_format($data->$field, 0) . "</td>"
                        : "<td $style>" .  number_format($data->$field, 2) . "</td>";
                }
            }
        } else {
            foreach ($schema as $ignored) {
                $cells .= "<td style='padding: 5px 8px;'>N/A</td>";
            }
        }

        return $cells;
    }

    /**
     * @param $currentData
     * @param $previousData
     * @param $totalDiff
     * @return string
     */
    protected function renderDiffCell($currentData, $previousData, &$totalDiff): string
    {
        $style = "style='padding: 5px 8px; text-align: center; background: #fff3d1;'";
        if ($currentData && $previousData) {
            $diff = $currentData->actual_amount_bdt - $previousData->actual_amount_bdt;
            $totalDiff += $diff;
            $diffFormatted = number_format($diff, 2);
            $diffColor = $diff < 0 ? 'color: red;' : '';
            return "<td $style><span style='{$diffColor}'>" . $diffFormatted . "</span></td>";
        }

        $value = 0;
        if (isset($currentData->actual_amount_bdt)) {
            $value = $currentData->actual_amount_bdt;
        } elseif (isset($previousData->actual_amount_bdt)) {
            $value = $previousData->actual_amount_bdt;
        }

        $totalDiff += $value;
        $diffColor = $value < 0 ? 'color: red;' : '';
        return "<td $style><span style='{$diffColor}'>" . number_format($value, 2) . "</span></td>";
    }

    /**
     * @param array $schema
     * @param array $totals
     * @return string
     */
    protected function renderTotalRow(array $schema, array $totals): string
    {
        $row = "<tr>";
        foreach ($schema as $field) {
            if ($field != 'traffic_date') {
                $row .= $field === 'successful_call' ?
                    "<td style='padding: 5px 8px; text-align: right;'><b>" . number_format($totals['current'][$field], 0) . "</b></td>" :
                    "<td style='padding: 5px 8px; text-align: right;'><b>" . number_format($totals['current'][$field], 2) . "</b></td>";
            } else {
                $row .= "<td style='padding: 5px 8px; text-align: left;'><b>Total</b></td>";
            }
        }
        $diffStyle = $totals['diff'] < 0 ? "style='color: red;'" : "";
        $row .= "<td style='padding: 5px 8px; text-align: center; background: #fff3d1;'><b $diffStyle>" . number_format($totals['diff'], 2) . "</b></td>";
        foreach ($schema as $field) {
            if ($field != 'traffic_date') {
                $row .= $field === 'successful_call' ?
                    "<td style='padding: 5px 8px; text-align: right;'><b>" . number_format($totals['previous'][$field], 0) . "</b></td>" :
                    "<td style='padding: 5px 8px; text-align: right;'><b>" . number_format($totals['previous'][$field], 2) . "</b></td>";
            } else {
                $row .= "<td style='padding: 5px 8px; text-align: left;'><b>Total</b></td>";
            }
        }

        $row .= "</tr>";

        return $row;
    }

    /**
     * @param $currentMonthResult
     * @return string
     */
    protected function currentMonthProjection($currentMonthResult): string
    {
        // Initialize the total amount.
        $totalAmount = 0;

        foreach($currentMonthResult as $data) {
            $totalAmount += $data->actual_amount_bdt;
        }

        $currentDate = Carbon::now()->format('Ymd');
        // First date of the month
        $firstDateOfMonth = Carbon::now()->startOfMonth()->format('Ymd');
        // Second date of the month
        $secondDateOfMonth = Carbon::now()->startOfMonth()->addDay()->format('Ymd');

        if($currentDate === $firstDateOfMonth) {
            $values = array_slice($currentMonthResult, 0, 1, true);
        } elseif ($currentDate === $secondDateOfMonth) {
            $values = array_slice($currentMonthResult, 0, 2, true);
        } else {
            $values = array_slice($currentMonthResult, -3, 3, true);
        }

        // Initialize the total amount for last 3 days
        $sum = 0;
        $totalValues = count($values);

        foreach ($values as $value) {
            $sum += $value->actual_amount_bdt;
        }

        $now            = Carbon::now();
        $endOfMonth     = $now->copy()->endOfMonth();
        $remainingDays  = $now->diffInDays($endOfMonth);

        $projection = number_format($totalAmount + (($remainingDays+1) * ($sum/$totalValues)), 2);

        $currentMonth   = $this->dateFormat($now, 'F-Y');
        $subMonth       = $this->dateFormat($now->subMonth(), 'F-Y');
        $style          = "style='background: #e8f9ff; border: 1px solid #3699bc; font-size:18px; padding: 8px; margin-bottom: 15px; color: #024157;'";

        // Calculate and return the projected total amount for the current month
        $result = ($firstDateOfMonth === $currentDate) ?
                    "Total profit of $subMonth: <b>$projection</b> BDT" :
                    "Projection for $currentMonth: <b>$projection</b> BDT";

        return "<span $style> $result </span><br>";

    }
}
