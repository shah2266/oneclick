<?php

namespace App\Http\Controllers\IGWANDIOS;

use App\Mail\IgwAndIosMails\SendComparisonReport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Authors\AuthorInformation;
use RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;

class ComparisonReportController extends Controller
{
    private $inExcel;
    private $outExcel;
    private $screenshot;

    public function __construct()
    {
        $this->inExcel = new Spreadsheet();
        $this->outExcel = new Spreadsheet();
        $this->screenshot = new Spreadsheet();
    }

    //All generated files show in view
    public function index() {
        $getFiles = Storage::disk('public')->files('platform/igwandios/comparison/main/');

        $files = array();
        foreach ($getFiles as $file) {
            $split = explode('/', $file);
            array_push($files, $split[4]);
        }


        $getSummaryFiles = Storage::disk('public')->files('platform/igwandios/comparison/summary/');

        $summaryFiles = array();
        foreach ($getSummaryFiles as $file) {
            $split = explode('/', $file);
            array_push($summaryFiles, $split[4]);
        }
        return view('platform.igwandios.comparison',['files'=>$files, 'summaryFiles' => $summaryFiles ]);
    }

    //Download IOS Daily Comparison Report
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igwandios/comparison/main/'.$filename;
        $headers = [
            'Content-Type' => 'application/ms-excel',
            ];

        return response()->download($file);
    }


    //Download IOS Daily Comparison Report

    /**
     * @param $filename
     * @return BinaryFileResponse
     */
    public function downloadSummary($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igwandios/comparison/summary/'.$filename;
        $headers = [
            'Content-Type' => 'application/ms-excel',
            ];

        return response()->download($file);
    }


    //Delete Generated Report
    public function deleteFile($filename): RedirectResponse
    {
       Storage::disk('public')->delete('/platform/igwandios/comparison/main/'.$filename);
        return Redirect::to('/platform/igwandios/report/comparison')->with('success','Report Successfully Deleted');
    }

    //Delete Generated Report
    public function deleteSummaryFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igwandios/comparison/summary/'.$filename);
        return Redirect::to('/platform/igwandios/report/comparison')->with('success','Report Successfully Deleted');
    }

    //Zip Download IOS Daily Comparison Report
    public function zipCreator() {
        $date = 'IGW and IOS Comparison '. Carbon::now()->subdays(1)->format('d-M-Y');
        $zip_file = public_path().'/platform/igwandios/ZipFiles/comparison/'.$date.'.zip'; //Store all created zip files here
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igwandios/comparison/main/';

        $files = new \RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $flag = 0;
        foreach ($files as $file) {
            // We're skipping all subFolders

            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $date.'/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
                $flag = 1;
            }

        }

        if($flag == 0) {
            return Redirect::to('/platform/igwandios/report/comparison')->with('danger','Directory is empty. Please generate reports');
        } else {
            $zip->close();
            return response()->download($zip_file);
        }
    }

    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igwandios/comparison/main/'));
        $clean2 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igwandios/comparison/summary/'));
        if($clean1 && $clean2) {
            return Redirect::to('/platform/igwandios/report/comparison')->with('success','All reports successfully deleted');
        } else {
            return Redirect::to('/platform/igwandios/report/comparison')->with('danger','There are a problem to delete files');
        }
    }

    /**
     * DB Query
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function igwIncomingQuery($fromDate, $toDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
            ->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
            ->join('ROUTE as r', 'cm.OutgoingRouteID', '=', 'r.RouteID')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), 'c.ShortName', 'r.RouteName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"), DB::raw("(SUM(cm.CallDuration)/60) as Duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',1)
            ->where('cm.OutCompanyID','=',3680)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"), 'c.ShortName','r.RouteName')
            ->get();
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function iosIncomingQuery($fromDate, $toDate): Collection
    {
        return DB::connection('sqlsrv2')->table('CallSummary as cm')
            ->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
            ->join('ROUTE as r', 'cm.IncomingRouteID', '=', 'r.RouteID')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), 'c.ShortName', 'r.RouteName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"), DB::raw("(SUM(cm.CallDuration)/60) as Duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',1)
            ->where('cm.InCompanyID','=',2)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"), 'c.ShortName','r.RouteName')
            ->get();
    }


    /**
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function igwOutgoingQuery($fromDate, $toDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        //dd($igwOutgoing);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
            ->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
            ->join('ROUTE as r', 'cm.IncomingRouteID', '=', 'r.RouteID')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), 'c.ShortName', 'r.RouteName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"), DB::raw("(SUM(cm.CallDuration)/60) as Duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',2)
            ->where('cm.InCompanyID','=',3680)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"), 'c.ShortName','r.RouteName')
            ->get();

    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function iosOutgoingQuery($fromDate, $toDate): Collection
    {
        //dd($iosOutgoing);
        return DB::connection('sqlsrv2')->table('CallSummary as cm')
            ->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
            ->join('ROUTE as r', 'cm.OutgoingRouteID', '=', 'r.RouteID')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), 'c.ShortName', 'r.RouteName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"), DB::raw("(SUM(cm.CallDuration)/60) as Duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',2)
            ->where('cm.OutCompanyID','=',2)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"), 'c.ShortName','r.RouteName')
            ->get();
    }

    /**
     * @param $dbName
     * @param $dbSchema
     * @param $companyId
     * @param $trafficDirection
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function query($dbName, $dbSchema, $companyId, $trafficDirection, $fromDate, $toDate): Collection
    {
        //dd($iosOutgoing);
        return DB::connection($dbName)->table('CallSummary as cm')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"), DB::raw("(SUM(cm.CallDuration)/60) as Duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',$trafficDirection)
            ->where('cm.'.$dbSchema,'=',$companyId)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"), 'cm.'.$dbSchema)
            ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),'ASC')
            ->get();
    }

    /**
     * Worksheet Formatting and Styling
     */

    //Report main heading row number (1)
    /**
     * @return array[]
     */
    public function reportHeading(): array
    {

        //$ht (heading top) style
        return [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'name' => 'Arial',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ];
    }

    //Report Details Heading Top row number (2 - 4)

    /**
     * @return array[]
     */
    public function reportDetailsHeading(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial',
            ],
        ];
    }

    //Table Heading Style

    /**
     * @return array[]
     */
    public function tableHeadingStyle(): array
    {
        //$thead (table heading)
        return [
            'font' => [
                'bold' => true,
                'size' => 13,
                'name' => 'Arial',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
    }

    //Table Heading Style

    /**
     * @return array[]
     */
    public function centerAlignment(): array
    {
        //$thead (table heading)
        return [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ];
    }

    //Set Border and text bold style

    /**
     * @return array
     */
    public function borderAndTextBold(): array
    {

        return [
           'font' => [
                'bold' => 'true',
                'size' => 10,
                'name' => 'Arial',
           ],
           'borders' => [
               'allBorders' => [
                   'borderStyle' => Border::BORDER_THIN,
               ],
           ],
       ];
   }

    //Set Border and text bold style

    /**
     * @return array
     */
    public function screenshotHeading(): array
    {

        return [
           'font' => [
                'bold' => 'true',
                'size' => 10,
                'name' => 'Arial',
           ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
           'borders' => [
               'allBorders' => [
                   'borderStyle' => Border::BORDER_THIN,
               ],
           ],
       ];
   }

    /**
     * @return array
     */
    public function allDataCenterAlign(): array
    {
       return [
           'alignment' => [
               'horizontal' => Alignment::HORIZONTAL_CENTER,
           ],
           'borders' => [
               'allBorders' => [
                   'borderStyle' => Border::BORDER_THIN,
               ],
           ]
       ];

   }

    /**
     * @return array
     */
    public function comparisonTableStyle(): array
    {

       return [
               'fill' => [
                   'fillType' => Fill::FILL_SOLID,
                   'startColor' => [
                       'argb' => 'F3FF00',
                   ],
               ],

               'alignment' => [
                   'horizontal' => Alignment::HORIZONTAL_CENTER,
               ],

               'font' => [
                       'bold' => 'true',
                       'size' => 10,
                       'name' => 'Arial',
                       //'color' => ['argb' => 'ff0000'],
               ],
               'borders' => [
                   'allBorders' => [
                       'borderStyle' => Border::BORDER_THIN,
                   ],
               ],
           ];

    }

    //All Borders

    /**
     * @return \array[][]
     */
    public function border(): array
    {

        return [
           'borders' => [
               'allBorders' => [
                   'borderStyle' => Border::BORDER_THIN,
               ],
           ],
       ];

    }

    /**
     * @return array
     */
    public function backgroundAndColor(): array
    {
        return [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFF00')
                ],
                'font' => [
                    'color' => ['rgb' => 'E50000']
                ]
            ];
    }

    /**
     * @param $spreadsheet
     * @param $trafficDirection
     * @param $platformName
     * @return string[]
     */
    public function tableHeading($spreadsheet, $trafficDirection, $platformName): array
    {
        $heading = array();
        $indexName = array();

        $indexOne = array('A','B','C','D','E');
        $indexTwo = array('H','I','J','K','L');

        $headingOne = array('Traffic Date','In Company Name','In Route Name','No of  Call','Dur(Min)');
        $headingTwo = array('Traffic Date','Out Company Name','Out Route Name','No of  Call','Dur(Min)');

        if($trafficDirection == 1 && $platformName == 'IGW') {
            $heading = $headingTwo;
            $indexName = $indexOne;
        } elseif($trafficDirection == 2 && $platformName == 'IGW') {
            $heading = $headingOne;
            $indexName = $indexOne;
        } elseif($trafficDirection == 1 && $platformName == 'IOS') {
            $heading = $headingOne;
            $indexName = $indexTwo;
        } else {
            $heading = $headingTwo;
            $indexName = $indexTwo;
        }

        foreach($heading as $key => $cellValue) {
            $this->$spreadsheet->getActiveSheet()->setCellValue($indexName[$key].'7',$cellValue);
        }

        return $heading;
    }

    /**
     * @param $spreadsheet
     * @return bool
     */
    public function cellAutoResize($spreadsheet): bool
    {
        //Get worksheet cell index
        $cellIndex = array('A','B','C','D','E','F','G','H','I','J','K','L');
        //Cells AutoResize
        foreach($cellIndex as $index) {
            $this->$spreadsheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }
        return true;
    }

    //Set Report Traffic Direction
    private function setTrafficDirection($direction) {
        return $direction;
    }

    //Text Alignment

    /**
     * @param $spreadsheet
     * @param $endIndex
     * @return void
     */
    private function leftAlignment($spreadsheet, $endIndex): void
    {
        $startIndex = ['A8', 'H8'];
        foreach($startIndex as $key => $cellNumber) {
            $this->$spreadsheet->getActiveSheet()->getStyle($cellNumber.':'.$endIndex[$key])
                               ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }

    //Number comma Separator

    /**
     * @param $spreadsheet
     * @param $endIndex
     * @return void
     */
    private function commaSeparated($spreadsheet, $endIndex): void
    {
        $startIndex = ['D8', 'K8'];
        foreach($startIndex as $key => $cellNumber) {
            $this->$spreadsheet->getActiveSheet()->getStyle($cellNumber.':'.$endIndex[$key])
                               ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
        }
    }

    //Set Author Info
    protected function authorInfo(string $spreadsheet) {
        $authorsInfo = AuthorInformation::authors();
        $this->$spreadsheet->getProperties()
                        ->setCreator($authorsInfo['creator'])
                        ->setLastModifiedBy($authorsInfo['creator'])
                        ->setTitle($authorsInfo['sTitle'])
                        ->setSubject($authorsInfo['sSubject'])
                        ->setDescription($authorsInfo['sDescription'])
                        ->setKeywords($authorsInfo['sKeywords'])
                        ->setCategory($authorsInfo['sCategory']);

        return $this->$spreadsheet;
    }

    /**
     * @param $spreadsheet
     * @param $igwRowNumber
     * @param $iosRowNumber
     * @return bool
     */
    public function comparisonTable($spreadsheet, $igwRowNumber, $iosRowNumber): bool
    {
        //(8 is Main Report starting row number)
        //Compare which query to gather then maximum row
        $getMaxRowNumber = max($igwRowNumber, $iosRowNumber);

        //get max row
        $totalRow = $getMaxRowNumber+3; // Add Extra 3 row with max row

        $this->$spreadsheet->getActiveSheet()->getStyle('F'.$totalRow.':G'.($totalRow+2))->applyFromArray($this->comparisonTableStyle());
        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('F'.($totalRow+2).':G'.($totalRow+2))->applyFromArray([
                                    'font' => [
                                        'color' => ['argb' => 'ff0000']
                                    ]
                                ])
                          ->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);


        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('F'.$totalRow,'IGW - IOS')
                          ->mergeCells('F'.$totalRow.':G'.$totalRow)
                          ->getStyle('F'.$totalRow.':G'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('F'.($totalRow+1),'No of  Call');

        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('G'.($totalRow+1),'Dur(Min)');

        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('F'.$totalRow.':G'.($totalRow+2))
                          ->applyFromArray($this->borderAndTextBold());

        //Get IGW and IOS (Total Calls Sum) Cell Index $value1 is IGW and $value2 is IOS
        $value1 = 'D'.$igwRowNumber;
        $value2 = 'K'.$iosRowNumber;

        //Get IGW and IOS (Total Min Sum) Cell Index $value3 is IGW and $value4 is IOS
        $value3 = 'E'.$igwRowNumber;
        $value4 = 'L'.$iosRowNumber;
        $this->$spreadsheet->getActiveSheet()->setCellValue('F'.($totalRow+2), "=$value1-$value2");
        $this->$spreadsheet->getActiveSheet()->setCellValue('G'.($totalRow+2), "=$value3-$value4");

        //return $this->spreadsheet;
        return true;
    }

    /**
     * @param $spreadsheet
     * @param $query
     * @param array $cellIndex
     * @return void
     */
    private function cellWiseDataSet($spreadsheet, $query, array $cellIndex=array()): void
    {
        //dd($cellIndex);
        //data Retrieve schema
        $tableSchema = array('trafficDate','ShortName','RouteName','SuccessfulCall','Duration');

        //Cell Data Set Starting Coordinate number
        $cellCoordinate = 8;
        //Query wise data retrieve and set cell value
        foreach($query as $cellValue) {

            for($j = 0; $j <= 4; $j++) {

                $schemaName = $tableSchema[$j];
                $this->$spreadsheet->getActiveSheet()
                                    ->setCellValue($cellIndex[$j].$cellCoordinate, $cellValue->$schemaName);
            }

            $cellCoordinate++; //Cell Coordinate increment 1 for each row
        }
    }

    public function worksheetStyle($spreadsheet, $fromDateForReport, $toDateForReport) {
        //Cell Styling
        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('A1:B1')
                          ->applyFromArray($this->reportHeading());

        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('A2:B4')
                          ->applyFromArray($this->reportDetailsHeading());
        //Set Header Default Title
        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('A1','Traffic Summary')
                          ->mergeCells('A1:B1');

        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('A2','From Date: '.$fromDateForReport)->mergeCells('A2:B2');

        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('A3','To Date: '.$toDateForReport)->mergeCells('A3:B3');

        //$this->spreadsheet->getActiveSheet()->setCellValue('A4', $this->setTrafficDirection('Direction: Int. Incoming'))->mergeCells('A4:B4');

        //Company name in the table
        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('A6','IGW')
                          ->mergeCells('A6:E6')
                          ->getStyle('A6:E6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->$spreadsheet->getActiveSheet()
                          ->setCellValue('H6','IOS')
                          ->mergeCells('H6:L6')
                          ->getStyle('H6:L6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        //Set border and text bold
        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('A6:E7')
                          ->applyFromArray($this->borderAndTextBold());

        $this->$spreadsheet->getActiveSheet()
                          ->getStyle('H6:L7')
                          ->applyFromArray($this->borderAndTextBold());

        return $this->$spreadsheet;
        //return true;
    }

    /**
     * @param $getFromDate
     * @param $getToDate
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function incomingReport($getFromDate, $getToDate, bool $scheduleGenerateType = false): bool
    {

        //Input Date formatting
        //$fromDate = '20180101'; //Input date
        $fromDate = Carbon::parse($getFromDate)->format('Ymd'); //Input date
        $toDate = Carbon::parse($getToDate)->format('Ymd'); //Input date

        $fromDateForReport = Carbon::parse($getFromDate)->format('d M Y');
        $toDateForReport = Carbon::parse($getToDate)->format('d M Y');

        //IGW Queries
        $igwIncoming = $this->igwIncomingQuery($fromDate, $toDate);
        //IOS Queries
        $iosIncoming = $this->iosIncomingQuery($fromDate, $toDate);

        //Report Start to End Total Row number count (8 is Main Report starting row number)
        $totalIGWRow = trim(count($igwIncoming)+8); //(8 is Main Report starting row number)
        $totalIOSRow = trim(count($iosIncoming)+8);

        //Worksheet prepare
        //Creator Information
        $this->authorInfo('inExcel');
        //Set Column AutoResize
        $this->cellAutoResize('inExcel');

        //Worksheet Initial Setting
        $this->inExcel->setActiveSheetIndex(0);
        $this->inExcel->getActiveSheet()->setTitle('Incoming (IGW - IOS)');
        $this->inExcel = $this->worksheetStyle('inExcel',$fromDateForReport, $toDateForReport);
        $this->inExcel->getActiveSheet()->setCellValue('A4', $this->setTrafficDirection('Direction: Int. Incoming'))->mergeCells('A4:B4');
        $this->leftAlignment('inExcel', ['A' . ($totalIGWRow - 1), 'L' . ($totalIOSRow - 1)]);
        $this->commaSeparated('inExcel', ['E' . $totalIGWRow, 'L' . $totalIOSRow]);

        //Data Container Table Heading
        $this->tableHeading('inExcel',1, 'IGW');
        $this->tableHeading('inExcel',1, 'IOS');

        //Get worksheet cell index
        $cellIndexForIGW = array('A','B','C','D','E');
        //Prepare IGW Incoming Report
        $this->cellWiseDataSet('inExcel',$igwIncoming,$cellIndexForIGW);
        //Summation
        //last Cell Value
        $this->inExcel->getActiveSheet()
                          ->getStyle('A8:E'.($totalIGWRow-1)) //(-1) is (total working row - 1) Actually it will set border without report table header and summation section
                          ->applyFromArray($this->border());

        $this->inExcel->getActiveSheet()
                            ->setCellValue('A'.$totalIGWRow, 'Total:')  //Total
                            ->mergeCells('A'.$totalIGWRow.':C'.$totalIGWRow)
                            ->getStyle('A'.$totalIGWRow.':E'.$totalIGWRow)
                            ->applyFromArray($this->borderAndTextBold());

        $this->inExcel->getActiveSheet()->setCellValue('D'.$totalIGWRow, '=SUM(D8:D'.($totalIGWRow-1).')');
        $this->inExcel->getActiveSheet()->setCellValue('E'.$totalIGWRow, '=SUM(E8:E'.($totalIGWRow-1).')');

        //End IGW Report

        //Prepare IOS Incoming Report
        $cellIndexForIOS = array('H','I','J','K','L');
        $this->cellWiseDataSet('inExcel',$iosIncoming,$cellIndexForIOS);

        //Summation
        //last Cell Value
        $this->inExcel->getActiveSheet()
                          ->getStyle('H8:L'.($totalIOSRow-1)) //(-1) is (total working row - 1) Actually it will set border without report table header and summation section
                          ->applyFromArray($this->border());

        $this->inExcel->getActiveSheet()
                            ->setCellValue('H'.$totalIOSRow, 'Total:')  //Total
                            ->mergeCells('H'.$totalIOSRow.':J'.$totalIOSRow)
                            ->getStyle('H'.$totalIOSRow.':L'.$totalIOSRow)
                            ->applyFromArray($this->borderAndTextBold());

        $this->inExcel->getActiveSheet()->setCellValue('K'.$totalIOSRow, '=SUM(K8:K'.($totalIOSRow-1).')');
        $this->inExcel->getActiveSheet()->setCellValue('L'.$totalIOSRow, '=SUM(L8:L'.($totalIOSRow-1).')');
        //End IOS Report

        /**
         * Comparison Table
         */
        $this->comparisonTable('inExcel', $totalIGWRow, $totalIOSRow);

        //End Comparison Table
        $filename = 'Incoming (IGW-IOS) '. Carbon::parse($toDateForReport)->format('d-M-Y');
        $writer = new Xlsx($this->inExcel);
        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/igwandios/schedule/comparison/'.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igwandios/comparison/main/'.$filename.'.xlsx');
        }

        //Disconnect Worksheets from memory
        $this->inExcel->disconnectWorksheets();
        unset($this->inExcel);

        return true;
        //return Redirect::to('/platform/igwandios/report/comparison')->with('message','('.\Carbon\Carbon::parse($request->reportDate)->format('d m Y').') Report Successfully Generated.');
    }


    /**
     * @param $getFromDate
     * @param $getToDate
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function outgoingReport($getFromDate, $getToDate, bool $scheduleGenerateType = false): bool
    {
        //Input Date formatting
        //$fromDate = '20180101'; //Input date
        $fromDate = Carbon::parse($getFromDate)->format('Ymd'); //Input date
        $toDate = Carbon::parse($getToDate)->format('Ymd'); //Input date

        $fromDateForReport = Carbon::parse($getFromDate)->format('d M Y');
        $toDateForReport = Carbon::parse($getToDate)->format('d M Y');

        //IGW Queries
        $igwOutgoing = $this->igwOutgoingQuery($fromDate, $toDate);

        //IOS Queries
        $iosOutgoing = $this->iosOutgoingQuery($fromDate, $toDate);

        //Report Start to End Total Row number count (8 is Main Report starting row number)
        $totalIGWRow = trim(count($igwOutgoing)+8); //(8 is Main Report starting row number)
        $totalIOSRow = trim(count($iosOutgoing)+8);


        //dd($totalIGWRow);
        //Worksheet prepare
        //Creator Information
        $this->authorInfo('outExcel');
        //Set Column AutoResize
        $this->cellAutoResize('outExcel');
        //dd($igwDBTableSchema);

        //Worksheet Initial Setting
        $this->outExcel->setActiveSheetIndex(0);
        $this->outExcel->getActiveSheet()->setTitle('Outgoing (IGW - IOS)');
        $this->outExcel = $this->worksheetStyle('outExcel',$fromDateForReport, $toDateForReport);
        $this->outExcel->getActiveSheet()->setCellValue('A4', $this->setTrafficDirection('Direction: Int. Outgoing'))->mergeCells('A4:B4');
        $this->leftAlignment('outExcel', ['A' . ($totalIGWRow - 1), 'L' . ($totalIOSRow - 1)]);
        $this->commaSeparated('outExcel', ['E' . $totalIGWRow, 'L' . $totalIOSRow]);

        //Data Container Table Heading
        $this->tableHeading('outExcel',2, 'IGW');
        $this->tableHeading('outExcel',2, 'IOS');

        //Get worksheet cell index
        $cellIndexForIGW = array('A','B','C','D','E');
        //Prepare IGW Incoming Report
        $this->cellWiseDataSet('outExcel',$igwOutgoing,$cellIndexForIGW);

        //Summation
        //last Cell Value
        $this->outExcel->getActiveSheet()
                          ->getStyle('A8:E'.($totalIGWRow-1)) //(-1) is (total working row - 1) Actually it will set border without report table header and summation section
                          ->applyFromArray($this->border());

        $this->outExcel->getActiveSheet()
                            ->setCellValue('A'.$totalIGWRow, 'Total:')  //Total
                            ->mergeCells('A'.$totalIGWRow.':C'.$totalIGWRow)
                            ->getStyle('A'.$totalIGWRow.':E'.$totalIGWRow)
                            ->applyFromArray($this->borderAndTextBold());

        $this->outExcel->getActiveSheet()->setCellValue('D'.$totalIGWRow, '=SUM(D8:D'.($totalIGWRow-1).')');
        $this->outExcel->getActiveSheet()->setCellValue('E'.$totalIGWRow, '=SUM(E8:E'.($totalIGWRow-1).')');

        //End IGW Report

        //Prepare IOS Incoming Report
        $cellIndexForIOS = array('H','I','J','K','L');
        $this->cellWiseDataSet('outExcel',$iosOutgoing,$cellIndexForIOS);

        //Summation
        //last Cell Value
        $this->outExcel->getActiveSheet()
                          ->getStyle('H8:L'.($totalIOSRow-1)) //(-1) is (total working row - 1) Actually it will set border without report table header and summation section
                          ->applyFromArray($this->border());

        $this->outExcel->getActiveSheet()
                            ->setCellValue('H'.$totalIOSRow, 'Total:')  //Total
                            ->mergeCells('H'.$totalIOSRow.':J'.$totalIOSRow)
                            ->getStyle('H'.$totalIOSRow.':L'.$totalIOSRow)
                            ->applyFromArray($this->borderAndTextBold());

        $this->outExcel->getActiveSheet()->setCellValue('K'.$totalIOSRow, '=SUM(K8:K'.($totalIOSRow-1).')');
        $this->outExcel->getActiveSheet()->setCellValue('L'.$totalIOSRow, '=SUM(L8:L'.($totalIOSRow-1).')');
        //End IOS Report

        /**
         * Comparison Table
         */
        $this->comparisonTable('outExcel',$totalIGWRow, $totalIOSRow);
        //End Comparison Table
        //$filename = 'Outgoing Diff IGW and IOS';
        $filename = 'Outgoing (IGW-IOS) '. Carbon::parse($toDateForReport)->format('d-M-Y');
        $writer = new Xlsx($this->outExcel);

        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/igwandios/schedule/comparison/'.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igwandios/comparison/main/'.$filename.'.xlsx');
        }


        //Disconnect Worksheets from memory
        $this->outExcel->disconnectWorksheets();
        unset($this->outExcel);

        return true;
    }

    //Screenshot create
    //Smart Heading creator
    /**
     * @throws Exception
     */
    private function smartHeadingCreator($startIndex, $direction) {
        //Heading Array Declare
        $indexArray = array('C','D','E','F','G','H','I','J'); //Screenshot index area
        $tableHeadingOne = array($direction);
        $tableHeadingTwo = array('IGW','Diff (IGW-IOS)','IOS');
        $columnNameArray = array('Date', 'No of Call', 'Dur(Min)','No of Call', 'Dur(Min)','Date','No of Call','Dur(Min)');

        $headingContainerArray = array($tableHeadingOne, $tableHeadingTwo, $columnNameArray);
        $indexStartingPosition = $startIndex;

        $this->screenshot->getActiveSheet()->setCellValue('C'.($startIndex-2),$direction.':')->mergeCells('C'.($startIndex-2).':D'.($startIndex-2))->getStyle('C'.($startIndex-2))->getFont()->applyFromArray(
            [
                'bold' => TRUE,
                'underline' => Font::UNDERLINE_SINGLE
            ]
        );

        //Auto resize
        foreach($indexArray as $index) {
            $this->screenshot->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        foreach($headingContainerArray as $value) {

            if(count($value) == 1) {
                foreach($value as $data) {
                    $this->screenshot->getActiveSheet()
                                    ->setCellValue(reset($indexArray).$indexStartingPosition, $data)
                                    ->mergeCells(reset($indexArray).$indexStartingPosition.':'.end($indexArray).$indexStartingPosition)
                                    ->getStyle(reset($indexArray).$indexStartingPosition.':'.end($indexArray).$indexStartingPosition)->applyFromArray($this->screenshotHeading());
                }
            } else if(count($value) == 3) {
                //dd($value);
                foreach($value as $keys => $data) {

                    if($keys == 0) {
                        $this->screenshot->getActiveSheet()
                                            ->setCellValue($indexArray[$keys].($indexStartingPosition+1), $data)
                                            ->mergeCells($indexArray[$keys].($indexStartingPosition+1).':'.$indexArray[$keys+2].($indexStartingPosition+1))
                                            ->getStyle($indexArray[$keys].($indexStartingPosition+1).':'.$indexArray[$keys+2].($indexStartingPosition+1))->applyFromArray($this->screenshotHeading());
                    } else if($keys == 1) {
                        $this->screenshot->getActiveSheet()
                                            ->setCellValue($indexArray[3].($indexStartingPosition+1), $data)
                                            ->mergeCells($indexArray[3].($indexStartingPosition+1).':'.$indexArray[4].($indexStartingPosition+1))
                                            ->getStyle($indexArray[3].($indexStartingPosition+1).':'.$indexArray[4].($indexStartingPosition+1))->applyFromArray($this->screenshotHeading());
                    } else {
                        $this->screenshot->getActiveSheet()
                                            ->setCellValue($indexArray[5].($indexStartingPosition+1), $data)
                                            ->mergeCells($indexArray[5].($indexStartingPosition+1).':'.$indexArray[7].($indexStartingPosition+1))
                                            ->getStyle($indexArray[5].($indexStartingPosition+1).':'.$indexArray[7].($indexStartingPosition+1))->applyFromArray($this->screenshotHeading());
                    }
                }
            } else {
                foreach($value as $keys => $data) {
                        $this->screenshot->getActiveSheet()
                                            ->setCellValue($indexArray[$keys].($indexStartingPosition+2), $data)
                                            ->getStyle(reset($indexArray).($indexStartingPosition+2).':'.end($indexArray).($indexStartingPosition+2))->applyFromArray($this->screenshotHeading());

                }
            }
        }
    }

    public function dataAttachedInMailBody($fromDate, $toDate): array
    {
        //$fromDate = '20231206'; //Input date
        //$toDate = '20231206'; //Input date

        //Dynamic query calling
        //IGW->IOS incoming
        $q1 = $this->query('sqlsrv1', 'OutCompanyID',3680,1,$fromDate, $toDate)->toArray();

        //IGW->IOS Incoming
        $q2 = $this->query('sqlsrv2', 'InCompanyID',2,1,$fromDate,$toDate)->toArray();


        //IGW->IOS Outgoing
        $q3 = $this->query('sqlsrv1', 'InCompanyID',3680,2,$fromDate,$toDate)->toArray();
        //IGW->IOS Outgoing
        $q4 = $this->query('sqlsrv2', 'OutCompanyID',2,2,$fromDate,$toDate)->toArray();

        $incoming = $this->calcCallDetails($q1, $q2);
        $outgoing = $this->calcCallDetails($q3, $q4);

        //return view('emails.comparison-report', compact('incoming','outgoing'));
        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ];
    }

    /**
     * @param $query1
     * @param $query2
     * @return array
     */
    public function calcCallDetails($query1, $query2): array
    {
        $data = array_slice(array_unique(array_merge(
            array_values(get_object_vars(array_merge($query1, $query2)[0])),
            array_values(get_object_vars(array_merge($query1, $query2)[1]))
        )),1);

        //dump(array_combine(array_values(get_object_vars(array_merge($q1, $q2)[0])), array_values(get_object_vars(array_merge($q1, $q2)[1]))));
        $calc = array($data[1] -  $data[3], $data[2] -  $data[4], $data[0]);
        array_splice($data, 3, 0, $calc);

        return $data;
    }



    /**
     * @param $getFromDate
     * @param $getToDate
     * @return void
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function screenShot($getFromDate, $getToDate): void
    {
        //Input Date formatting
        //$fromDate = '20190815'; //Input date
        $fromDate = Carbon::parse($getFromDate)->format('Ymd'); //Input date
        $toDate = Carbon::parse($getToDate)->format('Ymd'); //Input date

        //Dynamic query calling
        //IGW->IOS incoming
        $q1 = $this->query('sqlsrv1', 'OutCompanyID',3680,1,$fromDate, $toDate);

        //IGW->IOS Incoming
        $q2 = $this->query('sqlsrv2', 'InCompanyID',2,1,$fromDate,$toDate);


        //IGW->IOS Outgoing
        $q3 = $this->query('sqlsrv1', 'InCompanyID',3680,2,$fromDate,$toDate);
        //IGW->IOS Outgoing
        $q4 = $this->query('sqlsrv2', 'OutCompanyID',2,2,$fromDate,$toDate);

        //Creator Information
        $this->authorInfo('screenshot');

        //Worksheet Initial Setting
        $this->screenshot->setActiveSheetIndex(0);
        $this->screenshot->getActiveSheet()->setTitle('Reports Summary');
        //Cell Styling
        $this->screenshot->getActiveSheet()
                        ->getStyle('A2:B4')
                        ->applyFromArray($this->reportDetailsHeading());
        //Set Header Default Title
        $this->screenshot->getActiveSheet()
                         ->setCellValue('C2','IGW vs IOS Min Difference:')
                         ->mergeCells('C2:F2')
                         ->getStyle('C2:F2')
                         ->applyFromArray($this->reportHeading());

        //Create Incoming Header
        $this->smartHeadingCreator(6,'Incoming'); // 6 is incoming table heading starting cell index

        //Create Outgoing Header
        $OGReportCellIndex = (count($q1)+4)+8; //total day count by sql query, add 4 with sql return result for add extra 4 cell, 8 is end of the Incoming table heading cell index
        $this->smartHeadingCreator($OGReportCellIndex,'Outgoing');

        $query = array($q1, $q2, $q3, $q4);

        //Cell Data Set Starting Coordinate number
        //Set initial cell index
        $cellCoordinate1 = 9;
        $cellCoordinate2 = 9;
        $cellCoordinate3 = $OGReportCellIndex+3; // 3 is Outgoing Table heading total cell area containing
        $cellCoordinate4 = $OGReportCellIndex+3;

        //Query wise data retrieve and set in the Excel cell
        $tableSchema = array('trafficDate','SuccessfulCall','Duration');
        $indexArray = array('C','D','E','F','G','H','I','J'); //Screenshot index area
        foreach($query as $key => $dataAsArray) {
            if($key == 0 || $key == 1) { // This logic is matching with the query array-index, suppose, 0 or 1 is array index number. So I have to matched with the query array ($key) for getting perfect query or result
                foreach($dataAsArray as $data) {
                    if($key == 0) {
                        //This section is working with the first index in a query-array. May be, it's a IGW incoming query. and below loop set up the value in a specific cell index.
                        for($x = 0; $x <= 2; $x++) {
                            $schemaName = trim($tableSchema[$x]);
                            $this->screenshot->getActiveSheet()->setCellValue($indexArray[$x].$cellCoordinate1, $data->$schemaName);
                        }
                        $cellCoordinate1++;
                    } else {
                        for($j = 3; $j <= 7; $j++) {
                            if($j > 4) {
                                $schemaName = trim($tableSchema[$j-5]);
                                $this->screenshot->getActiveSheet()->setCellValue($indexArray[$j].$cellCoordinate2, $data->$schemaName);
                            } else {
                                //estimation cell index for row wise comparing and its auto comparing the both (IGW - IOS) side data, and it's incremental
                                $value1 = 'D'.$cellCoordinate2;
                                $value2 = 'E'.$cellCoordinate2;
                                $value3 = 'I'.$cellCoordinate2;
                                $value4 = 'J'.$cellCoordinate2;
                                $this->screenshot->getActiveSheet()->setCellValue('F'.$cellCoordinate2, "=$value1-$value3");
                                $this->screenshot->getActiveSheet()->setCellValue('G'.$cellCoordinate2, "=$value2-$value4");
                            }
                        }
                        $cellCoordinate2++;
                    }

                }
                //Set Style
                $incomingLastCellIndex = (count($q1)+6)+3; // Count total query value, I have counted first query in query-array. it's free to count other query-in-an-array, because all queries return same result. there is the number of (6), is a staring incoming cell index. and additional 3 is added for smooth report styling.
                $this->screenshot->getActiveSheet()->getStyle('C'.(6+3).':J'.($incomingLastCellIndex-1))->applyFromArray($this->allDataCenterAlign());
                $this->screenshot->getActiveSheet()->getStyle('D'.(6+3).':G'.($incomingLastCellIndex-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
                $this->screenshot->getActiveSheet()->getStyle('I'.(6+3).':J'.($incomingLastCellIndex-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
                $this->screenshot->getActiveSheet()->getStyle('F'.(6+3).':G'.($incomingLastCellIndex-1))->applyFromArray($this->backgroundAndColor());
            } else {
                foreach($dataAsArray as $data) {
                    if($key == 2) {
                        for($x = 0; $x <= 2; $x++) {
                            $schemaName = trim($tableSchema[$x]);
                            $this->screenshot->getActiveSheet()->setCellValue($indexArray[$x].$cellCoordinate3, $data->$schemaName);
                        }
                        $cellCoordinate3++;
                    } else {

                        for($j = 3; $j <= 7; $j++) {
                            if($j > 4) {
                                $schemaName = trim($tableSchema[$j-5]);
                                $this->screenshot->getActiveSheet()->setCellValue($indexArray[$j].$cellCoordinate4, $data->$schemaName);
                            } else {
                                //estimation cell index for row wise comparing and its auto comparing the both (IGW - IOS) side data, and it's incremental
                                $value1 = 'D'.$cellCoordinate4;
                                $value2 = 'E'.$cellCoordinate4;
                                $value3 = 'I'.$cellCoordinate4;
                                $value4 = 'J'.$cellCoordinate4;

                                $this->screenshot->getActiveSheet()->setCellValue('F'.$cellCoordinate4, "=$value1-$value3");
                                $this->screenshot->getActiveSheet()->setCellValue('G'.$cellCoordinate4, "=$value2-$value4");
                            }
                        }

                        $cellCoordinate4++;

                    }
                }
                //Set Style
                $this->screenshot->getActiveSheet()->getStyle('C'.($OGReportCellIndex+3).':J'.($cellCoordinate4-1))->applyFromArray($this->allDataCenterAlign());
                $this->screenshot->getActiveSheet()->getStyle('D'.($OGReportCellIndex+3).':G'.($cellCoordinate4-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
                $this->screenshot->getActiveSheet()->getStyle('I'.($OGReportCellIndex+3).':J'.($cellCoordinate4-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
                $this->screenshot->getActiveSheet()->getStyle('F'.($OGReportCellIndex+3).':G'.($cellCoordinate4-1))->applyFromArray($this->backgroundAndColor());
            }

        }
        //dd("test");
        $filename = 'IGW_and_IOS_comparison_summary '. Carbon::parse($toDate)->format('d-M-Y');
        $writer = new Xlsx($this->screenshot);
        $writer->save(public_path().'/platform/igwandios/comparison/summary/'.$filename.'.xlsx');
    }

    // Function to get all the dates in given range

    /**
     * @param $start
     * @param $end
     * @return array
     */
    private function getDatesFromRange($start, $end): array
    {
        $from = Carbon::parse($start);
        $to = Carbon::parse($end);

        // Declare an empty array
        $dates = array();

        for($d = $from; $d->lte($to); $d->addDay()) {
            $dates[] = $d->format('d-m-Y');
        }

        // Return the array elements
        return $dates;
    }


    public $incoming;
    public $outgoing;
    //Generate All reports :)

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     * @throws ValidationException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reportGenerate(Request $request): RedirectResponse
    {
        //Validation
        $this->validate($request, [
            'fromDate' => 'required',
            'toDate' => 'required'
        ]);

        // Function call with passing the start date and end date
        $dates = $this->getDatesFromRange($request->fromDate, $request->toDate);

        $processStartTime = microtime(TRUE);

        foreach($dates as $date) {
            $fromDate = $date;
            $toDate = $date;
            $this->incoming = $this->incomingReport($fromDate, $toDate);
            $this->outgoing = $this->outgoingReport($fromDate, $toDate);
        }

        $this->screenShot($request->fromDate, $request->toDate);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        if($this->incoming && $this->outgoing) {
            return Redirect::to('/platform/igwandios/report/comparison')->with('success',"Report generated! Process execution time: $executionTime seconds");
        }
    }
}
