<?php

namespace App\Http\Controllers\IGWANDIOS;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Authors\AuthorInformation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;

class IofInOutBoundReportController extends Controller
{

    private $spreadsheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    /**
     * Worksheet Formatting and Styling
     */

    //Heading Top Title Style
    public function headingTopStyle(): array
    {

        //$ht (heading top) style
        return [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'name' => 'Calibri',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ];
    }

    //Information title style
    public function infoTitle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 14,
                'name' => 'Calibri',
            ],
        ];
    }

    //Table Heading Style
    public function tableHeadingStyle(): array
    {
        //$thead (table heading)
        return [
            'font' => [
                'bold' => true,
                'size' => 13,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
    }

    //Table Heading Style
    public function tableBodyStyle(): array
    {
        //$thead (table heading)
        return [
                    'font' => [
                        'size' => 11,
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ];
    }

    //All Borders
    public function allBorderStyle(): array
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
     * @return array[]
     */
    public function numberFormated(): array
    {
        //Date Format
        return [
            'formatCode' => [
                'NumberFormat' => NumberFormat::FORMAT_NUMBER_COMMA,
            ],
        ];
    }

    /**
     * IGW DB Query
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    //IGW Day Wise Incoming
    private function igwIncomingQuery($fromDate, $toDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
                ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
                ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                ->where('cm.ReportTrafficDirection','=',1)
                ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),'ASC')
                ->get();
    }

    //IGW Day Wise Outgoing

    /**
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function igwOutgoingQuery($fromDate, $toDate): Collection
    {

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
                ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
                ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                ->where('cm.ReportTrafficDirection','=',2)
                ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),'ASC')
                ->get();
    }

    /**
     * IOS DB Query
     * sqlsrv2 -> IOS DB Connection
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */

    //IOS Day Wise Incoming
    private function iosIncomingQuery($fromDate, $toDate): Collection
    {
        return DB::connection('sqlsrv2')->table('CallSummary as cm')
                ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
                ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                ->where('cm.ReportTrafficDirection','=',1)
                ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), 'ASC')
                ->get();
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return Collection
     */
    private function iosOutgoingQuery($fromDate, $toDate): Collection
    {
        //IOS Day Wise Outgoing
        return DB::connection('sqlsrv2')->table('CallSummary as cm')
                ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
                ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                ->where('cm.ReportTrafficDirection','=',2)
                ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), 'ASC')
                ->get();
    }

    /**
     * @param $coverArea
     * @return Spreadsheet
     * @throws Exception
     */
    public function worksheetStyle($coverArea): Spreadsheet
    {
        //Cell Styling
        $this->spreadsheet->getActiveSheet()
                            ->getStyle('B3:F4')
                            ->applyFromArray($this->headingTopStyle());

        $this->spreadsheet->getActiveSheet()
                        ->getStyle('B5:F'.$coverArea)
                        ->applyFromArray($this->tableBodyStyle());

        $this->spreadsheet->getActiveSheet()
                        ->getStyle('B3:F'.$coverArea)
                        ->applyFromArray($this->allBorderStyle());

        $this->spreadsheet->getActiveSheet()
                        ->getStyle('C5:F'.$coverArea)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);

        //Set Header Default Title
        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('B3','Bangla Trac Communications Limited');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('B4','Date');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('C3','Incoming')
                        ->mergeCells('C3:D3');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('C4','IOS Minutes');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('D4','IGW Minutes');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('E3','Outgoing')
                        ->mergeCells('E3:F3');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('E4','IOS Minutes');

        $this->spreadsheet->getActiveSheet()
                        ->setCellValue('F4','IGW Minutes');
        return $this->spreadsheet;
    }

    //View loading
    public function index() {

        $getFiles = Storage::disk('public')->files('platform/igwandios/iof/inoutbound/');

        $files = array();
        foreach ($getFiles as $key => $file) {
            //dump($file);
            $split = explode('/', $file);
            array_push($files, $split[4]);
        }

        //dd($getFiles);

        return view('platform.igwandios.iof.Inoutbound.index', compact('files'));
    }

    //Download IOS Daily Report
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igwandios/iof/inoutbound/'.$filename;

        $headers = [
            'Content-Type' => 'application/ms-excel',
            ];

        return response()->download($file);
    }

    //Delete Generated Report
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igwandios/iof/inoutbound/'.$filename);
        return Redirect::to('platform/igwandios/report/iof/inoutbound/')->with('success','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'IOF in-out bound '. Carbon::now()->subdays(1)->format('d-M-Y');
        $zip_file =  public_path(). '/platform/igwandios/iof/ZipFiles/inoutbound/'.$date.'.zip'; //Store all created zip files here
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igwandios/iof/inoutbound/';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $flag = 0;
        foreach ($files as $name => $file) {
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
            return Redirect::to('platform/igwandios/report/iof/inoutbound/')->with('danger','Directory is empty. Please generate reports');
        } else {
            $zip->close();
            return response()->download($zip_file);
        }
    }

    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igwandios/iof/inoutbound/'));
        if($clean1) {
            return Redirect::to('platform/igwandios/report/iof/inoutbound/')->with('success','All Reports Successfully Deleted');
        } else {
            return Redirect::to('platform/igwandios/report/iof/inoutbound/')->with('danger','There a problem to delete files');
        }
    }


    //In-Out Bound Reports prepare

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reports($inputDate = null, bool $scheduleGenerateType = false): RedirectResponse
    {

        //Validation check
//        $this->validate($request, [
//            'reportDate' => 'required',
//        ]);

        $reportDate = request()->reportDate ?? $inputDate;


        //Input Date formatting
        $fromDate = Carbon::parse($reportDate)->subDays(30)->format('Ymd'); //Getting previous days upto 9
        $toDate = Carbon::parse($reportDate)->format('Ymd'); //Input date

        $processStartTime = microtime(TRUE);

        //Generate Mail body screenshot file
        if(!$scheduleGenerateType) {
            $this->mailBodyReport($reportDate);
        }

        //IGW Queries
        $igwIncoming = $this->igwIncomingQuery($fromDate, $toDate);
        $igwOutgoing = $this->igwOutgoingQuery($fromDate, $toDate);

        //IOS Queries
        $iosIncoming = $this->iosIncomingQuery($fromDate, $toDate);
        $iosOutgoing = $this->iosOutgoingQuery($fromDate, $toDate);

        //DB Query Container Array
        $db_Query = array('1'=>$iosIncoming, '2'=>$igwIncoming, '3'=>$iosOutgoing, '4'=>$igwOutgoing);

        //DB Column Container Array
        $db_column = array('trafficDate', 'successfulCall', 'duration');
        //DB Query Testing
        //dd($test);

        //Worksheet prepare
        //Creator Information
        $authorsInfo = AuthorInformation::authors();
        $this->spreadsheet->getProperties()
                    ->setCreator($authorsInfo['creator'])
                    ->setLastModifiedBy($authorsInfo['creator'])
                    ->setTitle($authorsInfo['sTitle'])
                    ->setSubject($authorsInfo['sSubject'])
                    ->setDescription($authorsInfo['sDescription'])
                    ->setKeywords($authorsInfo['sKeywords'])
                    ->setCategory($authorsInfo['sCategory']);

        //Get worksheet cell index
        $cellIndex = array('B','C','D','E','F');

        $this->spreadsheet->setActiveSheetIndex(0);
        $this->spreadsheet->getActiveSheet()->setTitle('BanglaTrac');
        $this->spreadsheet = $this->worksheetStyle((count($igwIncoming)+4));

        //Cells AutoResize
        foreach($cellIndex as $key => $index) {
            $this->spreadsheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        //Cell Data Set Starting Coordinate number
        $cellCoordinate = 5;
        foreach($iosIncoming as $key => $cellValue) {
            $this->spreadsheet->getActiveSheet()
                              ->setCellValue($cellIndex[0].$cellCoordinate, Carbon::parse($iosIncoming[$key]->trafficDate)->format('l, F d, Y'));
            $cellCoordinate++;
        }

        //Loop Execute: Total number sql-query in an array
        for($i = 1; $i <= count($db_Query); $i++ ) {

            //Cell Data Set Starting Coordinate number
            $cellCoordinate = 5;

            //Query wise data retrieve and set cell value
            foreach($db_Query[$i] as $key => $cellValue) {
                $this->spreadsheet->getActiveSheet()
                                  ->setCellValue($cellIndex[$i].$cellCoordinate, $cellValue->duration);
                $cellCoordinate++; //Cell Coordinate increment 1 for each row
            }

        }

        $filename = 'BTrac IOS IN OUT Call Summary';
        $writer = new Xlsx($this->spreadsheet);
        //File Stored Directory

        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/igwandios/iof/schedule/inoutbound/'.$filename.' '.Carbon::parse($toDate)->format('d-M-Y').'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igwandios/iof/inoutbound/'.$filename.' '.Carbon::parse($toDate)->format('d-M-Y').'.xlsx');
        }
        //Disconnect Worksheets from memory
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        return Redirect::to('platform/igwandios/report/iof/inoutbound')->with('success',"Report generated! Process execution time: $executionTime Seconds");

    }

    private function mailBodyReport($getDate): bool
    {
        //Input Date formatting
        $fromDateSh = Carbon::parse($getDate)->subDays(9)->format('Ymd'); //Getting previous days upto 9
        $toDateSh = Carbon::parse($getDate)->format('Ymd'); //Input date
        //$dayAfter = date('Y-m-d', strtotime("-10 days"));

        //IGW Queries
        $igwIncoming = $this->igwIncomingQuery($fromDateSh, $toDateSh);
        $igwOutgoing = $this->igwOutgoingQuery($fromDateSh, $toDateSh);

        //IOS Queries
        $iosIncoming = $this->iosIncomingQuery($fromDateSh, $toDateSh);
        $iosOutgoing = $this->iosOutgoingQuery($fromDateSh, $toDateSh);

        //DB Query Container Array
        $db_Query = array('1'=>$iosIncoming, '2'=>$igwIncoming, '3'=>$iosOutgoing, '4'=>$igwOutgoing);

        //DB Column Container Array
        $db_column = array('trafficDate', 'successfulCall', 'duration');
        //DB Query Testing
        //dd($test);

        //Worksheet prepare
        //Creator Information
        $authorsInfo = AuthorInformation::authors();
        $this->spreadsheet->getProperties()
                    ->setCreator($authorsInfo['creator'])
                    ->setLastModifiedBy($authorsInfo['creator'])
                    ->setTitle($authorsInfo['sTitle'])
                    ->setSubject($authorsInfo['sSubject'])
                    ->setDescription($authorsInfo['sDescription'])
                    ->setKeywords($authorsInfo['sKeywords'])
                    ->setCategory($authorsInfo['sCategory']);


        //Get worksheet cell index
        $cellIndex = array('B','C','D','E','F');

        $this->spreadsheet->setActiveSheetIndex(0);
        $this->spreadsheet->getActiveSheet()->setTitle('BanglaTrac');
        $this->spreadsheet = $this->worksheetStyle((count($igwIncoming)+4));

        //Cells AutoResize
        foreach($cellIndex as $key => $index) {
            $this->spreadsheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        //Cell Data Set Starting Coordinate number
        $cellCoordinate = 5;
        foreach($iosIncoming as $key => $cellValue) {
            $this->spreadsheet->getActiveSheet()
                                ->setCellValue($cellIndex[0].$cellCoordinate, Carbon::parse($iosIncoming[$key]->trafficDate)->format('l, F d, Y'));
            $cellCoordinate++;
        }

        //Loop Execute: Total number sql-query in an array
        for($i = 1; $i <= count($db_Query); $i++ ) {

            //Cell Data Set Starting Coordinate number
            $cellCoordinate = 5;

            //Query wise data retrieve and set cell value
            foreach($db_Query[$i] as $key => $cellValue) {
                $this->spreadsheet->getActiveSheet()
                                    ->setCellValue($cellIndex[$i].$cellCoordinate, $cellValue->duration);
                $cellCoordinate++; //Cell Coordinate increment 1 for each row
            }

        }

        //Filename
        $filename = 'IOF_In_Out_Bound_Screenshoot';
        $writer = new Xlsx($this->spreadsheet);

        //File Stored Directory
        $writer->save(public_path().'/platform/igwandios/iof/inoutbound/'.$filename.' '.Carbon::parse($toDateSh)->format('d-M-Y').'.xlsx');

        return true;
    }

    //Auto mail body content
    public function dataAttachedInMailBody ($date): array
    {
        //$date = '20231209';
        //Input Date formatting
        $fromDate = Carbon::parse($date)->subDays(9)->format('Ymd'); //Getting previous days upto 9
        $toDate = Carbon::parse($date)->format('Ymd'); //Input date
        //$dayAfter = date('Y-m-d', strtotime("-10 days"));

        //IGW Queries
        $igwIncoming = $this->igwIncomingQuery($fromDate, $toDate)->toArray();
        $igwOutgoing = $this->igwOutgoingQuery($fromDate, $toDate)->toArray();

        //IOS Queries
        $iosIncoming = $this->iosIncomingQuery($fromDate, $toDate)->toArray();
        $iosOutgoing = $this->iosOutgoingQuery($fromDate, $toDate)->toArray();

        //DB Query
        $organizedData = array_merge($iosIncoming, $igwIncoming, $iosOutgoing, $igwOutgoing);

        $resultArray = [];
        //dd($dataArray);
        foreach ($organizedData as $data) {
            $dataArray =array_slice(get_object_vars($data), 1);
            //dump($dataArray);

            // Organize the array by trafficDate
            foreach ($dataArray as $item) {
                $date = $dataArray["trafficDate"];
                if (!isset($resultArray[$date])) {
                    $resultArray[$date] = [];
                }
                $resultArray[$date][] = $item;
            }
        }

        //Test
        //dump($resultArray);
        $arrayKeys = array_keys($resultArray);

        $tableContent = "";
        for($i = 0; $i < count($arrayKeys); $i++) {

            $arr = $resultArray[$arrayKeys[$i]];
            //dump($arr);
            $tableContent .= "<tr>";
            $tableContent .= "<td>" . Carbon::parse($arrayKeys[$i])->format('l, F d, Y') . "</td>";
            for ($j = 0; $j < count($arr); $j++) {
                if($arr[$j] === $arrayKeys[$i]) {
                    continue;
                } else {
                    $j++;
                    $tableContent .= "<td>" . number_format($arr[$j], 2) . "</td>";
                }
            }

            $tableContent .= "</tr>";
        }

        //dd($tableContent);
        return [
            'tableContent' => $tableContent
        ];
        //return view('emails.iof-in-out-report', compact('tableContent'));
    }
}
