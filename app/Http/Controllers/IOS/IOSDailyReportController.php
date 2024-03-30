<?php

namespace App\Http\Controllers\IOS;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Authors\AuthorInformation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use Illuminate\Support\Facades\DB;

class IOSDailyReportController extends Controller
{
    public function index() {
        $getFiles = Storage::disk('public')->files('platform/ios/callsummary');

        $files = array();

        foreach ($getFiles as $file) {
            $fileData = explode("/", $file);
            array_push($files, end($fileData));
        }

        return view('platform.ios.iosReport', compact('files'));
    }

//    public function testingDate(Request $request) {
//
//        $this->validate($request, [
//            'reportDate' => 'required',
//        ]);
//        $inputDate = Carbon::parse($request->reportDate)->format('d M Y');
//
//       echo $fromDate = $inputDate.' 00:00:00';
//       echo "<br>";
//       echo $toDate = $inputDate.' 23:59:59';
//    }


    //Download IOS Daily Report
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/ios/callsummary/'.$filename;
        return response()->download($file);
    }

    //File move another folder
    public function moveFile($filename): RedirectResponse
    {

        if(!Storage::disk('public')->exists('/platform/ios/backup/callsummary/' . $filename)) {
            Storage::disk('public')->move('/platform/ios/callsummary/'.$filename, '/platform/ios/backup/callsummary/'.$filename);
            $message = 'Report successfully moved to "Backup" directory in public folder.';
            return Redirect::to('/platform/ios/report/callsummary')->with('success', $message);
        } else {
            $message = 'File already exists in this "Backup" directory';
            return Redirect::to('/platform/ios/report/callsummary')->with('danger', $message);
        }
    }

    //Permanently delete file from 'callsummary' and 'Backup' directory
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/ios/callsummary/'.$filename, '/platform/ios/backup/callsummary/'.$filename);
        return Redirect::to('/platform/ios/report/callsummary')->with('success','Report Successfully Deleted');
    }

    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/ios/callsummary'));
        if($clean1) {
            return Redirect::to('platform/ios/report/callsummary')->with('success','Report directory clean!');
        } else {
            return Redirect::to('platform/ios/report/callsummary')->with('danger','There are a problem to delete files');
        }
    }

    //Zip Download
    public function zipCreator() {
        $date = 'IOS daily call summary '. Carbon::now()->subdays()->format('d-M-Y');
        $zip_file =  public_path(). '/platform/ios/ZipFiles/callsummary/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/ios/callsummary/';

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $flag = 0;
        foreach ($files as $file) {
            // We're skipping all sub-folders

            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $date.'/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
                $flag = 1;
            }

        }

        if($flag == 0) {
            return Redirect::to('platform/igw/report/ioswise')->with('danger','Directory is empty. Please generate reports');
        } else {
            $zip->close();
            return response()->download($zip_file);
        }
    }

    //Report Generate Function


    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reportGenerate($date = null, string $directory = null, bool $scheduleGenerateType = false): RedirectResponse
    {

//        request()->validate([
//            'reportDate' => 'required',
//        ]);

        $reportDate = request()->reportDate ?? $date;

        $inputDate = Carbon::parse($reportDate)->format('Ymd');
        $fromDate = $inputDate.' 00:00:00';
        $toDate = $inputDate.' 23:59:59';

        $processStartTime = microtime(TRUE);

        //MSSQL-Queries
        $igwWiseIncoming = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as cn', 'cm.InCompanyID', '=', 'cn.CompanyID')
                        ->select('cn.ShortName as companyName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',1)
                        ->groupBy('cn.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('cn.ShortName','ASC')
                        ->get();

        $icxWiseIncoming = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as cn', 'cm.OutCompanyID', '=', 'cn.CompanyID')
                        ->select('cn.ShortName as companyName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',1)
                        ->groupBy('cn.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('cn.ShortName','ASC')
                        ->get();

        $ansWiseIncoming = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as cn', 'cm.ANSID', '=', 'cn.CompanyID')
                        ->select('cn.ShortName as companyName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',1)
                        ->groupBy('cn.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('cn.ShortName','ASC')
                        ->get();

        //Outgoing Report Query
        $igwWiseOutgoing = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as cn', 'cm.OutCompanyID', '=', 'cn.CompanyID')
                        ->select('cn.ShortName as companyName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',2)
                        ->groupBy('cn.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('cn.ShortName','ASC')
                        ->get();

        $icxWiseOutgoing = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as cn', 'cm.InCompanyID', '=', 'cn.CompanyID')
                        ->select('cn.ShortName as companyName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',2)
                        ->groupBy('cn.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('cn.ShortName','ASC')
                        ->get();

        $ansWiseOutgoing = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->select('cm.ANSID',DB::raw("(SELECT ShortName FROM Company WHERE cm.ANSID = CompanyID) as companyName"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration"), DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                        ->where('cm.ReportTrafficDirection','=',2)
                        ->groupBy('cm.ANSID', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('companyName','ASC')
                        ->get();
        //Data Testing
        //dd($ansWiseOutgoing);

        //Database schema
        $db_schema_arr = array('companyName','trafficDate','successfulCall','duration','ACD');
        $db_all_query = array('1'=>$icxWiseIncoming, '2'=> $ansWiseIncoming, '3'=> $igwWiseOutgoing,'4'=> $icxWiseOutgoing,'5'=> $ansWiseOutgoing);

        //DateRange not used
        $headingFromDate = Carbon::parse($reportDate)->format('d M Y').' 00:00:00';
        $headingToDate = Carbon::parse($reportDate)->format('d M Y').' 23:59:59';

        $spreadsheet = new Spreadsheet();

        //Top Heading Title Style
        //Table Heading Style
        //$thead (table heading)
        $text_right = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ];

        $topHeading = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Arial',
            ],
        ];
        //Top Bottom Heading Title Style
        $reportInfoTitle = [
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial',
            ],
        ];

        //Table Heading Style
        $tableHeading = [
            'font' => [
                'bold' => true,
                'size' => 10,
                'name'  => 'Arial',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ],
        ];

        //All Borders
        $allBorders = [
            'font' => [
                'size' => 10,
                'name'  => 'Arial',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        //$sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $numberOfWorkSheet = 5; //Worksheet indexing start from 0

        $cellIndexArr = array('B','C','D','E','F');

        //Worksheet Name Array
        $workSheetTitle = array (
            //'0' => 'IGW to BTrac IOS IN',
            '1' => 'BTrac IOS to ICX IN',
            '2' => 'BTrac IOS to ANS IN',
            '3' => 'BTrac IOS to IGW OUT',
            '4' => 'ICX to BTrac IOS OUT',
            '5' => 'ANS to BTrac IOS OUT'
        );

        //Creator Information
        $authorsInfo = AuthorInformation::authors();
        $spreadsheet->getProperties()
                    ->setCreator($authorsInfo['creator'])
                    ->setLastModifiedBy($authorsInfo['creator'])
                    ->setTitle($authorsInfo['sTitle'])
                    ->setSubject($authorsInfo['sSubject'])
                    ->setDescription($authorsInfo['sDescription'])
                    ->setKeywords($authorsInfo['sKeywords'])
                    ->setCategory($authorsInfo['sCategory']);

        //Default Active Worksheet 0
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle("IGW to BTrac IOS IN");

        //Dynamically Create Worksheet with unique name
        foreach($workSheetTitle as $name) {
            $spreadsheet->createSheet()->setTitle($name);
        }

        //Heading array
        $tbl_heading = array('A'=>'SN','B'=>'Company Name','C'=>'Traffic Date','D'=>'Successful Call','E'=>'Minutes','F'=>'ACD');

        //In Worksheet: Report Heading Title
        for($i = 0; $i <= $numberOfWorkSheet; $i++ ) {
            if($i == 0) {
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()
                            ->setCellValue('A1', 'Traffic Report by Company and Date')
                            ->mergeCells('A1:C1')
                            ->getStyle('A1:C1')
                            ->applyFromArray($topHeading);

                $spreadsheet->getActiveSheet()
                            ->setCellValue('A2', 'From Date: '.$headingFromDate)
                            ->mergeCells('A2:C2')
                            ->getStyle('A2:C2')
                            ->applyFromArray($reportInfoTitle);

                $spreadsheet->getActiveSheet()
                            ->setCellValue('A3', 'To Date: '.$headingToDate)
                            ->mergeCells('A3:C3')
                            ->getStyle('A3:C3')
                            ->applyFromArray($reportInfoTitle);

                $spreadsheet->getActiveSheet()
                            ->setCellValue('A4', 'Direction: Incoming')
                            ->mergeCells('A4:C4')
                            ->getStyle('A4:C4')
                            ->applyFromArray($reportInfoTitle);

                //Auto Cell Sizing
                foreach($cellIndexArr as $cellIndex) {
                    $spreadsheet->getActiveSheet()->getColumnDimension($cellIndex)->setAutoSize(true);
                }

                //Table Heading print and style
                foreach($tbl_heading as $key => $heading){
                    $spreadsheet->getActiveSheet()
                                ->setCellValue($key.'6', $heading)
                                ->getStyle('A6:F6')
                                ->applyFromArray($tableHeading);
                }

            } else {
                $spreadsheet->setActiveSheetIndex($i);
                $spreadsheet->getActiveSheet()
                            ->setCellValue('A1', 'Traffic Report by Company and Date')
                            ->mergeCells('A1:C1')
                            ->getStyle('A1:C1')
                            ->applyFromArray($topHeading);

                $spreadsheet->getActiveSheet()
                            ->setCellValue('A2', 'From Date: '.$headingFromDate)
                            ->mergeCells('A2:C2')
                            ->getStyle('A2:C2')
                            ->applyFromArray($reportInfoTitle);

                $spreadsheet->getActiveSheet()
                            ->setCellValue('A3', 'To Date: '.$headingToDate)
                            ->mergeCells('A3:C3')
                            ->getStyle('A3:C3')
                            ->applyFromArray($reportInfoTitle);
                if($i <= 2) {
                    $spreadsheet->getActiveSheet()
                            ->setCellValue('A4', 'Direction: Incoming')
                            ->mergeCells('A4:C4')
                            ->getStyle('A4:C4')
                            ->applyFromArray($reportInfoTitle);
                } else {
                    $spreadsheet->getActiveSheet()
                            ->setCellValue('A4', 'Direction: Outgoing')
                            ->mergeCells('A4:C4')
                            ->getStyle('A4:C4')
                            ->applyFromArray($reportInfoTitle);
                }

                //Auto Cell Sizing
                foreach($cellIndexArr as $cellIndex) {
                    $spreadsheet->getActiveSheet()->getColumnDimension($cellIndex)->setAutoSize(true);
                }

                //Table Heading print and style
                foreach($tbl_heading as $key => $heading){
                    $spreadsheet->getActiveSheet()
                                ->setCellValue($key.'6', $heading)
                                ->getStyle('A6:F6')
                                ->applyFromArray($tableHeading);
                }

            }

        }

        //Worksheet wise data set
        for($ws = 0; $ws <= 1; $ws++) {
            if($ws == 0) {

                //Cell Starting point
                $dataPosition = 7 ; //7 each cell number like A7, B7, C7 etc

                //First Worksheet 0
                $spreadsheet->setActiveSheetIndex(0);

                foreach($igwWiseIncoming as $key => $cellValue) {

                    //First Cell value
                    $spreadsheet->getActiveSheet()->setCellValue('A'.$dataPosition, $key+1); //First Column Serial Number echo

                    for($j = 0; $j <= 4; $j++){

                        $schemaName = $db_schema_arr[$j];
                        $spreadsheet->getActiveSheet()->setCellValue($cellIndexArr[$j].$dataPosition, $igwWiseIncoming[$key]-> $schemaName);

                    }

                    //1 Increment cell row
                    $dataPosition++;
                }

                //Table Style and Formatting
                $spreadsheet->getActiveSheet()
                            ->getStyle('A7:F'.(count($igwWiseIncoming)+6))
                            ->applyFromArray($allBorders);
                $spreadsheet->getActiveSheet()
                            ->getStyle('C7:C'.(count($igwWiseIncoming)+6))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY_US);

                $spreadsheet->getActiveSheet()
                            ->getStyle('C7:C'.(count($igwWiseIncoming)+6))
                            ->applyFromArray($text_right);

                $spreadsheet->getActiveSheet()
                            ->getStyle('D7:D'.(count($igwWiseIncoming)+7))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);

                $spreadsheet->getActiveSheet()
                            ->getStyle('E7:F'.(count($igwWiseIncoming)+7))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                //Summation
                //last Cell Value
                $spreadsheet->getActiveSheet()
                            ->setCellValue('A'.(count($igwWiseIncoming)+7), 'Total:')  //Total
                            ->mergeCells('A'.(count($igwWiseIncoming)+7).':C'.(count($igwWiseIncoming)+7))
                            ->getStyle('A'.(count($igwWiseIncoming)+7).':F'.(count($igwWiseIncoming)+7))
                            ->applyFromArray($tableHeading);
                $spreadsheet->getActiveSheet()->setCellValue('D'.(count($igwWiseIncoming)+7), '=SUM(D7:D'.(count($igwWiseIncoming)+6).')');
                $spreadsheet->getActiveSheet()->setCellValue('E'.(count($igwWiseIncoming)+7), '=SUM(E7:E'.(count($igwWiseIncoming)+6).')');
                $spreadsheet->getActiveSheet()->setCellValue('F'.(count($igwWiseIncoming)+7), '=E'.(count($igwWiseIncoming)+7).'/D'.(count($igwWiseIncoming)+7));
                //First Worksheet 0 end

            } else {

                for($q = 1; $q <= 5; $q++) {

                    //Dynamic worksheet create
                    $spreadsheet->setActiveSheetIndex($q);

                    //Cell Starting point
                    $dataPosition = 7 ; //7 each cell number like A7, B7, C7 etc

                    //Take each query from query array ($db_all_query) and split all data in a query
                    foreach($db_all_query[$q] as $key => $cellValue) {

                        //First Cell value
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$dataPosition, $key+1); //First Column Serial Number echo

                        //Cell coordinate change loop
                        for($j = 0; $j <= 4; $j++){

                            //Get db schema from $db_schema_arr
                            $schemaName = $db_schema_arr[$j];

                            //Cell wise data print
                            $spreadsheet->getActiveSheet()->setCellValue($cellIndexArr[$j].$dataPosition, $db_all_query[$q][$key]-> $schemaName);

                            //Roaming Call text check and print
                            if(($db_schema_arr[0] == 'companyName') && ($db_all_query[$q][$key]->$schemaName == '')) {

                                $spreadsheet->getActiveSheet()->setCellValue($cellIndexArr[$j].$dataPosition, 'Roaming Call');

                            }

                        }

                        //1 Increment cell row
                        $dataPosition++;
                    }

                //Table Style and Formatting
                $spreadsheet->getActiveSheet()
                            ->getStyle('A7:F'.(count($db_all_query[$q])+6))
                            ->applyFromArray($allBorders);

                $spreadsheet->getActiveSheet()
                            ->getStyle('C7:C'.(count($db_all_query[$q])+6))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY_US);

                $spreadsheet->getActiveSheet()
                            ->getStyle('C7:C'.(count($db_all_query[$q])+6))
                            ->applyFromArray($text_right);

                $spreadsheet->getActiveSheet()
                            ->getStyle('D7:D'.(count($db_all_query[$q])+7))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);

                $spreadsheet->getActiveSheet()
                            ->getStyle('E7:F'.(count($db_all_query[$q])+7))
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                //Summation
                //last Cell Value
                $spreadsheet->getActiveSheet()
                            ->setCellValue('A'.(count($db_all_query[$q])+7), 'Total:')  //Total
                            ->mergeCells('A'.(count($db_all_query[$q])+7).':C'.(count($db_all_query[$q])+7))
                            ->getStyle('A'.(count($db_all_query[$q])+7).':F'.(count($db_all_query[$q])+7))
                            ->applyFromArray($tableHeading);

                $spreadsheet->getActiveSheet()->setCellValue('D'.(count($db_all_query[$q])+7), '=SUM(D7:D'.(count($db_all_query[$q])+6).')');
                $spreadsheet->getActiveSheet()->setCellValue('E'.(count($db_all_query[$q])+7), '=SUM(E7:E'.(count($db_all_query[$q])+6).')');
                $spreadsheet->getActiveSheet()->setCellValue('F'.(count($db_all_query[$q])+7), '=E'.(count($db_all_query[$q])+7).'/D'.(count($db_all_query[$q])+7));
                //Dynamic worksheet create

                }
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        //Filename
        $filename = 'BTrac IOS IN OUT Call Summary '. Carbon::parse($reportDate)->format('d-M-Y');

        //File Stored Directory
		//$writer->save('C:/Users/User-PC/Desktop/IOSDailyReport/'.$filename.'.xlsx'); //This pc directory
        //$writer->save('C:/Users/shah.alam/Desktop/IOSDailyReport/'.$filename.'.xlsx'); //Shah Alam pc directory

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/ios/callsummary/'.$filename.'.xlsx');
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);
        return Redirect::to('/platform/ios/report/callsummary')->with('success',"Report generated! Process execution time: $executionTime Seconds");
    }
}
