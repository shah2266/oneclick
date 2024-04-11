<?php

namespace App\Http\Controllers\IOS;

use App\Http\Controllers\Controller;
use App\Models\IofCompany;
use App\Traits\SQLQueryServices;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Query\CallSummaryIncomingQuery;
use App\Query\CallSummaryOutgoingQuery;
use App\Authors\AuthorInformation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class IosBtrcMonthlyReportController extends Controller
{
    use SQLQueryServices;

    const CELL_NAME = 'A';
    const A_ASCII_VALUE = 65;
    const TABLE_HEADER_CELL = 6;
    const REPORT_FIRST_CELL = 7;

    /**
     * @var Spreadsheet
     */
    private $excel;


    public function __construct()
    {
        $this->excel = new Spreadsheet();
    }

    private function companyList(): array
    {
        //return [6, 8, 9, 11, 14, 16, 19, 23, 27, 30];
        return [6, 8, 9, 11];
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
            'Direction: ' . $direction
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
            'ICX Name',
            'No of Call',
            'Dur (Min)',
            'Bill Dur (Min)'
        ];
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
            'in-icx',
            'in-ans',
            'out-icx',
            'out-ans'
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
        $direction = 'Int. Incoming';

        foreach ($this->companyList() as $key => $companyId) {

            // Reinitialize $this->excel for each iteration
            $this->excel = new Spreadsheet();

            $result = $this->queries($startDate, $endDate, $companyId);

            $sheetName = $this->workSheetName();
            //dump($result);
            for($i = 0; $i < count($result); $i++) {
                if(!Collection::make($result[$i]['data'])->isEmpty()) {
                    ($i == 0) ? $this->excel->getActiveSheet()->setTitle($sheetName[$i]) : $this->excel->createSheet()->setTitle($sheetName[$i]);
                    $this->dataSetter($i, $startDate, $endDate, $direction, $result[$i]);
                }
            }

            $writer = new Xlsx($this->excel);

            $writer->save(public_path().'/platform/ios/btrcmonthlyreport/icxandanswise/companyId__' . $key . '__' . $companyId . '.xlsx');

        }
        dd('test');
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
            $this->excel->getActiveSheet()->setCellValue(self::CELL_NAME . ($key + 1), $value);
        }
    }

    private function setTableHeading($tableHeading)
    {
        foreach ($tableHeading as $i => $heading) {
            $this->excel->getActiveSheet()->setCellValue(chr(self::A_ASCII_VALUE + $i) . self::TABLE_HEADER_CELL, $heading);
        }
    }

    /**
     * @param $activeSheet
     * @param $startDate
     * @param $endDate
     * @param $direction
     * @param $queryResult
     * @return Spreadsheet
     * @throws Exception
     */
    public function dataSetter($activeSheet, $startDate, $endDate, $direction, $queryResult): Spreadsheet
    {

        $this->excel->setActiveSheetIndex($activeSheet); //Default active worksheet.

        $this->setReportHeading($this->reportHeading($startDate, $endDate, $direction));
        $this->setTableHeading($this->tableHeading());

        $schema = $this->dbSchema();
        $total_schema = count($this->dbSchema());

        foreach ($queryResult['data'] as $key => $data) {
            for($i=0; $i < $total_schema; $i++){
                $sch = (string) $schema[$i];
                $this->excel->getActiveSheet()->setCellValue(chr(self::A_ASCII_VALUE+$i).(self::REPORT_FIRST_CELL+$key), $data->$sch);
            }
        }

        $this->excel->setActiveSheetIndex(0); //Default active worksheet.

        return $this->excel;
    }

}
