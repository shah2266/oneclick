<?php

namespace App\Http\Controllers\IGW;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Query\CallSummaryIncomingQuery;
use App\Query\CallSummaryOutgoingQuery;
use App\Authors\AuthorInformation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BtrcReportController extends Controller
{
    //BTRC Report
    private $worksheetType;
    private $dayWiseExcel;
    private $iosAnsExcel;
    private $direction;
    public  $fromDate;
    public  $toDate;
    private $durationType;
    private $tableHeadingCellIndex = 7;
    private $reportStartCellIndex = 8;

    public function __construct() {
        $this->dayWiseExcel = new Spreadsheet();
        $this->iosAnsExcel  = new Spreadsheet();
    }

    //Set Border and text bold style
    private function fontStyle($bold=false, $size=10): array
    {
        return [
            'font' => [
                'name' => 'Calibri',
                'bold' => $bold,
                'size' => $size,
                'color' => [
                'rgb' => '000000'
                ]
            ]
        ];
    }

    //Data contains table style
    private function table(): array
    {
        return [
            'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
        ];
    }

    //Number Format
    private function formatNumber($format): array
    {
        return [
            //'formatCode' => NumberFormat::FORMAT_NUMBER_00
            'formatCode' => $format
        ];
    }

    //Right Alignment
    private function textRightAlignment(): array
    {
        return [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ];
    }

    /**
     * @return array[]
     */
    private function textCenterAlignment(): array
    {
        return [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::VERTICAL_CENTER,
            ]
        ];
    }

    //Set Author Info
    private function authorInfo($sheet): void
    {
        $authorsInfo = AuthorInformation::authors();
        $sheet->getProperties()
                ->setCreator($authorsInfo['creator'])
                ->setLastModifiedBy($authorsInfo['creator'])
                ->setTitle($authorsInfo['sTitle'])
                ->setSubject($authorsInfo['sSubject'])
                ->setDescription($authorsInfo['sDescription'])
                ->setKeywords($authorsInfo['sKeywords'])
                ->setCategory($authorsInfo['sCategory']);

    }

    //Set worksheet title
    // public function setWorksheetTitle($title) {
    //     $this->worksheetTitle = $title;
    // }

    // //Get worksheet title
    // public function getWorksheetTitle() {
    //     return $this->worksheetTitle;
    // }

    //Duration type (Actual or Bill)
    public function setDurationType(String $type) {
        $this->durationType = $type;
    }

    public function getDurationType() {
        return $this->durationType;
    }


    //Set from date
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;
    }

    //Get from date
    public function getFromDate()
    {
        return $this->fromDate;
    }

    //Set to date
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;
    }

    //Get to date
    public function getToDate()
    {
        return $this->toDate;
    }

    //Set direction
    public function setDirection($direction) {
        $this->direction = $direction;
    }

    //Get direction
    public function getDirection()
    {
        return $this->direction;
    }

    //Report top information

    /**
     * @return string[]
     */
    private function infoDetails(): array
    {
        $date = $this->getDirection() == 'International Incoming Calls' ? 'Period: '. Carbon::parse($this->getToDate())->format('d M Y') : 'Period From: '. Carbon::parse($this->getFromDate())->format('d M Y').' To '. Carbon::parse($this->getToDate())->format('d M Y');
        $title = $this->getDirection() == 'International Incoming Calls' ? 'IOS and ANS Wise Report' : 'Day wise traffic report for BTRC - '.$this->getDurationType().' Duration';

        return [
            '1' => $title,
            '2' => 'Reporting IGW: Bangla Trac Communications Limited',
            '3' => $date
        ];
    }

    /**
     * @return array|string[]
     */
    private function mergeInformation(): array
    {
        $heading = $this->infoDetails();
        $direction = $this->getDirection() == 'International Incoming Calls' ? $this->getDirection() : 'Direction: '.$this->getDirection();
        array_push($heading, $direction);
        return $heading;
    }

    //Set default worksheet name or title
    // private function defaultActiveWorksheet($sheet)
    // {
    //     return $sheet->setActiveSheetIndex(0)->setTitle($this->getWorksheetTitle()); //Worksheet Create
    // }

    //Set Worksheet Type (Incoming or Outgoing)
    public function setWorksheetType($type)
    {
        $this->worksheetType = $type;
    }

    //Get Worksheet Type (Incoming or Outgoing)
    public function getWorksheetType()
    {
        return $this->worksheetType;
    }

    //Get default worksheet, set title
    // private function getDefaultWorksheet()
    // {
    //     return $this->defaultActiveWorksheet($this->getWorksheetType());
    // }

    //Index auto size
    /**
     * @param $start
     * @param $end
     * @return bool
     */
    public function indexAutoSize($start, $end): bool
    {
        $sheet = $this->getWorksheetType();
        foreach(range($start, $end) as $index) {
            $sheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        return true;
    }

    //Heading setup

    /**
     * @return mixed
     */
    public function headingSetup()
    {
        $sheet      = $this->getWorksheetType();
        $headings   = $this->mergeInformation();

        foreach($headings as $key => $heading) {
            //dump($key.'-'.$heading);
            $cells = 'A'.$key.':D'.$key;
            $styles = $key == 1 ? $this->fontStyle(true, 12) : $this->fontStyle(false, 11);
            $sheet->getActiveSheet()->setCellValue('A'.$key, $heading)->mergeCells($cells)->getStyle($cells)->applyFromArray($styles);
        }

        return $sheet;
    }

    //Create day wise report table header
    public function createDayWiseTableHeader()
    {
        $sheet = $this->getWorksheetType();

        $headingName = array('A'=>'Sl No','B'=>'Date','C'=>'No of Calls','D'=>'Duration in Min','E'=>'No of Calls','F'=>'Duration in Min');

        $lastIndexCoordinate = 0;

        $titleStyle = array_merge($this->table(),$this->textCenterAlignment(),$this->fontStyle(true, 11));
        $sheet->getActiveSheet()->setCellValue('C6', 'IGW Incoming')->mergeCells('C6:D6')->getStyle('C6:D6')->applyFromArray($titleStyle);
        $sheet->getActiveSheet()->setCellValue('E6', 'IGW Outgoing')->mergeCells('E6:F6')->getStyle('E6:F6')->applyFromArray($titleStyle);

        //i = 65, 65 is a character 'A' ascii value
        for($i = 65; $i < (65+count($headingName)); $i++) {
            //$arrayKey = array_keys($headingName, $headingName[chr($i)]);
            //$cellIndex = $arrayKey[0].$this->tableHeadingCellIndex;
            $cellIndex = chr($i).$this->tableHeadingCellIndex;
            $lastIndexCoordinate = $cellIndex;
            $sheet->getActiveSheet()->setCellValue($cellIndex, $headingName[chr($i)])->getStyle($cellIndex)->applyFromArray(array_merge($this->textRightAlignment(), $this->fontStyle(true, 11)));
        }

        //Set Border
        $sheet->getActiveSheet()->getStyle('A'.$this->tableHeadingCellIndex.':'.$lastIndexCoordinate)->applyFromArray($this->table());

        return $sheet;
    }

    //Create IOS and ANS report table header
    public function createIosAndAnsTableHeader()
    {
        $sheet = $this->getWorksheetType();

        $headingName = array('A'=>'Name of ICX','B'=>'No. of Mins','C'=>'skip','D'=>'Name of ANS','E'=>'No. of Mins '); //E => 'No. of Mins ' A space after title for unique identification

        //i = 65, 65 is a character 'A' ascii value
        for($i = 65; $i < (65+count($headingName)); $i++) {
            $arrayKey = array_keys($headingName, $headingName[chr($i)]);
            //var_dump($headingName[chr($i)]);
            $cellIndex = $arrayKey[0].$this->tableHeadingCellIndex;

            if($cellIndex == 'C7') {
                continue;
            } else {
                $sheet->getActiveSheet()->setCellValue($cellIndex, $headingName[chr($i)])->getStyle($cellIndex)->applyFromArray($this->fontStyle(true, 11));
            }

        }

        //Set Border
        $sheet->getActiveSheet()->getStyle('A'.$this->tableHeadingCellIndex.':B'.$this->tableHeadingCellIndex)->applyFromArray($this->table());
        $sheet->getActiveSheet()->getStyle('D'.$this->tableHeadingCellIndex.':E'.$this->tableHeadingCellIndex)->applyFromArray($this->table());


        return $sheet;
    }

    //Incoming and Outgoing db schema

    /**
     * @return string[]
     */
    private function schema(): array
    {
        return ['traffic_date','SuccessfulCall','Duration'];
    }

    //Incoming Query
    private function incomingQuery(): Collection
    {
        return CallSummaryIncomingQuery::dayWiseIncoming($this->getFromDate(), $this->getToDate());
    }

    //Incoming Query
    private function outgoingQuery(): Collection
    {
        return CallSummaryOutgoingQuery::dayWiseOutgoing($this->getFromDate(), $this->getToDate());
    }

    private function outgoingBillDurationQuery(): Collection
    {
        return CallSummaryOutgoingQuery::dayWiseOutgoingWithBillDuration($this->getFromDate(), $this->getToDate());
    }

    //Day wise data set
    public function dayWiseDataSetter(Array $arr = null)
    {
        $sheet = $this->getWorksheetType();
        $schema = $this->schema(); //Get db schema
        $query = $arr; //Get db day wise query

        $lastIndexCoordinate = 0;

        //Query iteration
        for($q=0; $q < count($query); $q++) {

            $j = 0;
            foreach($query[$q] as $data) {
                //$count = 0;

                //Toggle cell index
                $indexName = $q == 0 ? '66' : '69'; //66 is B and 69 is E index ascii value

                //echo "<pre>";
                //var_dump($indexName);
                //Serial
                $sheet->getActiveSheet()->setCellValue('A'.($this->reportStartCellIndex+$j), ($j+1));
                //$count = $count+$j;
                for($i = 0; $i < count($schema); $i++) {

                    //Schema handle
                    if($q == 0) {
                        $name = $schema[$i];
                    } else {
                        if($schema[$i] == 'traffic_date') {
                            continue; //Skip Incoming traffic date schema and traffic date
                        } else {
                            $name = $schema[$i];
                        }
                    }

                    //get cell coordinate
                    $cellCoordinate = chr($indexName).($this->reportStartCellIndex+$j);
                    $lastIndexCoordinate = $cellCoordinate; //get last index with coordinate

                    //Cell wise date set
                    $sheet->getActiveSheet()
                            ->setCellValue($cellCoordinate, $data->$name)
                            ->getStyle($cellCoordinate)
                            ->getNumberFormat()
                            ->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));

                    //Traffic date format
                    if($name == 'traffic_date' && $q == 0) {
                        $sheet->getActiveSheet()->getStyle($cellCoordinate)->applyFromArray($this->textRightAlignment());
                        $sheet->getActiveSheet()->getStyle($cellCoordinate)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_DATE_XLSX15));
                    }

                    $indexName++;
                }

                $j++;
            }

            //Wrap table section
            $tableWrapIndex = 'A'.$this->reportStartCellIndex.':'.$lastIndexCoordinate;
            $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->fontStyle(false, 11));
            $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->table());
        }

        return $sheet;
    }

    //IOS Incoming Query
    private function iosIncomingQuery(): Collection
    {
        return CallSummaryIncomingQuery::IOSWiseIncoming($this->getFromDate(), $this->getToDate());
    }

    //ANS Incoming Query
    private function ansIncomingQuery(): Collection
    {
        return CallSummaryIncomingQuery::ANSWiseIncoming($this->getFromDate(), $this->getToDate());
    }

    public $lastIndexCoordinate;
    public function iosAndAnsDataSetter()
    {
        $sheet = $this->getWorksheetType();
        $schema = ['ShortName','Duration']; //Get db schema
        $query = [$this->iosIncomingQuery(), $this->ansIncomingQuery()]; //Get IOS and ANS wise query

        //Query iteration
        for($q=0; $q < count($query); $q++) {

            $j = 0;

            foreach($query[$q] as $data) {
                //$count = 0;

                //Toggle cell index
                $indexName = $q == 0 ? '65' : '68'; //65 is A and 68 is D index ascii value

                //$count = $count+$j;
                for($i = 0; $i < count($schema); $i++) {
                    //Schema handle
                    $name = $schema[$i];
                    $cellCoordinate = chr($indexName).($this->reportStartCellIndex+$j);

                    $this->lastIndexCoordinate = chr($indexName).(($this->reportStartCellIndex+$j)+1); //get last index with coordinate

                    //Cell wise date set
                    $sheet->getActiveSheet()
                            ->setCellValue($cellCoordinate, $data->$name)
                            ->getStyle($cellCoordinate)
                            ->getNumberFormat()
                            ->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));

                    $indexName++;
                }

                $j++;
            }

            //Wrap table section
            $tableWrapIndex = $q == 0 ? 'A'.$this->reportStartCellIndex.':'.$this->lastIndexCoordinate : 'D'.$this->reportStartCellIndex.':'.$this->lastIndexCoordinate;

            $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->fontStyle(false, 11));
            $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->table());


            $totalCoordinate = (count($query[$q])+$this->reportStartCellIndex);

            $total      = 'A'.$totalCoordinate;
            $total2     = 'D'.$totalCoordinate;
            $totalIndex = $q == 0 ? $total : $total2;
            $sheet->getActiveSheet()->setCellValue($totalIndex, 'Total:')->getStyle($totalIndex)->applyFromArray($this->fontStyle(true, 11));

            $valueSetIndex  = $q == 0 ? 'B'.$totalCoordinate : 'E'.$totalCoordinate;
            $totalMins      = '=SUM(B'.$this->reportStartCellIndex.':B'.($totalCoordinate-1).')';
            $totalMins2     = '=SUM(E'.$this->reportStartCellIndex.':E'.($totalCoordinate-1).')';

            $sum = $q == 0 ? $totalMins : $totalMins2;
            $sheet->getActiveSheet()->setCellValue($valueSetIndex, $sum)->getStyle($valueSetIndex)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));
            $sheet->getActiveSheet()->getStyle($valueSetIndex)->applyFromArray($this->fontStyle(true, 11));
        }

        //dd('End');
        return $sheet;

    }

    //Get reports

    /**
     * @return void
     */
    private function getHeader(): void
    {
       // $this->getDefaultWorksheet(); //Get worksheet title and default worksheet
        $this->indexAutoSize('A','F'); //Index auto size
        $this->headingSetup(); //top heading
    }

    /**
     * @return string[]
     */
    private function worksheetName(): array
    {
        return ['Traffic Report-Actual Minute', 'Traffic Report-Billable Minute'];
    }

    public function createDayWiseWorksheet() {
        $title = $this->worksheetName();
        $sheet = $this->getWorksheetType();

        for($i = 0; $i < count($this->worksheetName()); $i++) {
            if($i == 0) {
                $sheet->setActiveSheetIndex($i); //First Worksheet
                $sheet->getActiveSheet()->setTitle($title[$i]);
                $this->setDurationType('Actual');
                $this->getHeader(); //get reports
                $this->createDayWiseTableHeader(); //Create day wise report table header
                $this->dayWiseDataSetter([$this->incomingQuery(), $this->outgoingQuery()]); //Day wise Data setting
            } else {
                $sheet->createSheet()->setTitle($title[$i]);
                $sheet->setActiveSheetIndex($i);
                $this->setDurationType('Billed');
                $this->getHeader(); //get reports
                $this->createDayWiseTableHeader(); //Create day wise report table header
                $this->dayWiseDataSetter([$this->incomingQuery(), $this->outgoingBillDurationQuery()]); //Day wise Data setting
            }
        }

        return $sheet;

    }

    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @param string|null $directory
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function dayWiseReport($inputFromDate, $inputToDate, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->dayWiseExcel); //Authors
        $this->setDirection('Incoming and Outgoing'); //Direction or Type
        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date
        $this->setWorksheetType($this->dayWiseExcel); //Incoming excel
        $this->createDayWiseWorksheet();

        //Default Active Worksheet 0
        $this->dayWiseExcel->setActiveSheetIndex(0);
        $filename ='Daily Report BanglaTrac from '. Carbon::parse($this->getFromDate())->format('d-M-Y').' To '. Carbon::parse($this->getToDate())->format('d-M-Y');
        $writer = new Xlsx($this->dayWiseExcel);

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/btrc/'.$filename.'.xlsx');
        }

        return true;
    }

    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @param string|null $directory
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function iosAndAnsWiseReport($inputFromDate, $inputToDate, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->iosAnsExcel); //Authors
        $this->setDirection('International Incoming Calls'); //Direction or Type
        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date
        $this->setWorksheetType($this->iosAnsExcel); //Incoming excel
        $this->iosAnsExcel->setActiveSheetIndex(0)->setTitle('BTRAC IGW');
        $this->getHeader(); //get reports
        $this->createIosAndAnsTableHeader(); //Create IOS and ANS report table header
        $this->iosAndAnsDataSetter();
        //Default Active Worksheet 0
        $this->iosAnsExcel->setActiveSheetIndex(0);
        $filename ='IGW Bangla Trac '. Carbon::parse($this->getToDate())->format('d-M-Y');
        $writer = new Xlsx($this->iosAnsExcel);

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/btrc/'.$filename.'.xlsx');
        }

        return true;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reports() {

        //dd(request());
        request()->validate([
            'fromDate'   => 'required',
            'toDate'   => 'required',
        ]);

        //$inputFromDate          = \Carbon\Carbon::parse(request()->fromDate)->subDays(31)->format('Ymd');
        //$inputToDate            = \Carbon\Carbon::parse(request()->toDate)->format('Ymd');

        $inputFromDate          = Carbon::parse(request()->fromDate)->format('Ymd');
        $inputToDate            = Carbon::parse(request()->toDate)->format('Ymd');

        $fromDate = $inputFromDate.' 00:00:00'; //Get before date at least 32 day before
        $fromDate2 = $inputToDate.' 00:00:00';
        $toDate = $inputToDate.' 23:59:59';

        $processStartTime = microtime(TRUE);

        $this->dayWiseReport($fromDate, $toDate); //Day wise report
        $this->iosAndAnsWiseReport($fromDate2, $toDate); //Last day report

        //Disconnect Worksheets from memory
        $this->dayWiseExcel->disconnectWorksheets();
        $this->iosAnsExcel->disconnectWorksheets();
        unset($this->dayWiseExcel);
        unset($this->iosAnsExcel);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        return redirect('platform/igw/report/btrc')->with('success',"Report generated! Process execution time: $executionTime Seconds");
    }

    public function index() {
        $getFiles = Storage::disk('public')->files('platform/igw/btrc/');

        $files= array();

        foreach ($getFiles as $file) {
            $fileData = explode("/", $file);
            array_push($files, end($fileData));
        }

        return view('platform.igw.btrc.index', compact('files'));
    }

    //Download reports

    /**
     * @param $filename
     * @return BinaryFileResponse
     */
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igw/btrc/'.$filename;
        return response()->download($file);
    }

    //Delete Generated Report

    /**
     * @param $filename
     * @return RedirectResponse
     */
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igw/btrc/'.$filename);
        return Redirect::to('platform/igw/report/btrc')->with('message','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'BTRC '. Carbon::yesterday()->format('d-M-Y');
        $zip_file =  public_path(). '/platform/igw/ZipFiles/btrc/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igw/btrc/';

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
            return Redirect::to('platform/igw/report/btrc')->with('danger','Directory is empty. Please generate reports');
        } else {
            $zip->close();
            return response()->download($zip_file);
        }
    }

    //Clear Directory

    /**
     * @return RedirectResponse
     */
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igw/btrc/'));
        if($clean1) {
            return Redirect::to('platform/igw/report/btrc')->with('success','All Reports Successfully Deleted');
        } else {
            return Redirect::to('platform/igw/report/btrc')->with('danger','There are a problem to delete files');
        }
    }

}
