<?php

namespace App\Http\Controllers\IGW;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

class IOSReportController extends Controller
{
    //IOS Wise Report
    private $worksheetType;
    private $incomingExcel;
    private $outgoingExcel;
    private $heading = array();
    private $direction;
    public  $fromDate;
    public  $toDate;
    private $worksheetTitle;
    private $tableHeadingCellIndex = 6;
    private $reportStartCellIndex = 7;

    public function __construct() {
        $this->incomingExcel = new Spreadsheet();
        $this->outgoingExcel = new Spreadsheet();
    }

    //Set Border and text bold style

    /**
     * @param bool $bold
     * @param int $size
     * @return array[]
     */
    private function fontStyle(bool $bold=false, int $size=10): array
    {
        return [
                    'font' => [
                        'name' => 'Arial',
                        'bold' => $bold,
                        'size' => $size,
                        'color' => [
                        'rgb' => '000000'
                        ]
                    ]
                ];
    }

    //Data contains table style

    /**
     * @return \array[][]
     */
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

    /**
     * @param $format
     * @return array
     */
    private function formatNumber($format): array
    {
        return [
            //'formatCode' => NumberFormat::FORMAT_NUMBER_00
            'formatCode' => $format
        ];
    }

    //Data contains table header style

    /**
     * @return array
     */
    public function footer(): array
    {
        return [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 10,
                'color' => [
                'rgb' => '000000'
                ]
            ],
            'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
        ];
    }

    //Set Author Info
    private function authorInfo($sheet) {
        $authorsInfo = AuthorInformation::authors();
        $sheet->getProperties()
                ->setCreator($authorsInfo['creator'])
                ->setLastModifiedBy($authorsInfo['creator'])
                ->setTitle($authorsInfo['sTitle'])
                ->setSubject($authorsInfo['sSubject'])
                ->setDescription($authorsInfo['sDescription'])
                ->setKeywords($authorsInfo['sKeywords'])
                ->setCategory($authorsInfo['sCategory']);

        return $sheet;
    }

    //Set worksheet title
    public function setWorksheetTitle($title) {
        $this->worksheetTitle = $title;
    }

    //Get worksheet title
    public function getWorksheetTitle() {
        return $this->worksheetTitle;
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
        return [
            '1' => 'Traffic Report by Company and Date',
            '2' => 'From Date: '. Carbon::parse($this->getFromDate())->format('d M Y').' 00:00:00',
            '3' => 'To Date: '. Carbon::parse($this->getToDate())->format('d M Y').' 23:59:59'
        ];
    }

    /**
     * @return array|string[]
     */
    private function mergeInformation(): array
    {
        $this->heading = $this->infoDetails();
        array_push($this->heading, 'Direction: '.$this->getDirection());
        return $this->heading;
    }

    //Set default worksheet name or title
    private function defaultActiveWorksheet($sheet)
    {
        return $sheet->setActiveSheetIndex(0)->setTitle($this->getWorksheetTitle()); //Worksheet Create
    }

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
    private function getDefaultWorksheet()
    {
        return $this->defaultActiveWorksheet($this->getWorksheetType());
    }

    //Index auto size
    public function indexAutoSize($start, $end)
    {
        $sheet = $this->getWorksheetType();
        foreach(range($start, $end) as $index) {
            $sheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }
    }

    //Heading setup
    private function headingSetup()
    {
        $sheet      = $this->getWorksheetType();
        $headings   = $this->mergeInformation();

        foreach($headings as $key => $heading) {
            $cells = 'A'.$key.':D'.$key;
            $styles = $key == 1 ? $this->fontStyle(true, 12) : $this->fontStyle(true);
            $sheet->getActiveSheet()->setCellValue('A'.$key, $heading)->mergeCells($cells)->getStyle($cells)->applyFromArray($styles);
        }

        return $sheet;
    }

    //Create report table header
    private function createTableHeader()
    {
        $sheet = $this->getWorksheetType();

        $headingName = array('A'=>'SN','B'=>'Company Name','C'=>'Traffic Date','D'=>'Successful Call','E'=>'Minutes','F'=>'ACD');
        //$lastIndexCoordinate = "";
        //i = 65, 65 is a character 'A' ascii value
        for($i = 65; $i < (65+count($headingName)); $i++) {
            $arrayKey = array_keys($headingName, $headingName[chr($i)]);
            $cellIndex = $arrayKey[0].$this->tableHeadingCellIndex;
            $lastIndexCoordinate = $cellIndex;
            $sheet->getActiveSheet()->setCellValue($cellIndex, $headingName[chr($i)])->getStyle($cellIndex)->applyFromArray($this->fontStyle(true));
        }

        //Set Border
        $sheet->getActiveSheet()->getStyle('A'.$this->tableHeadingCellIndex.':'.$lastIndexCoordinate)->applyFromArray($this->table());

        return $sheet;
    }

    //Incoming and Outgoing db schema

    /**
     * @return string[]
     */
    private function schema(): array
    {
        return ['ShortName','TrafficDate','SuccessfulCall','Duration','ACD'];
    }

    //Incoming Query
    private function incomingQuery(): Collection
    {
        return CallSummaryIncomingQuery::IOSWiseIncoming($this->getFromDate(), $this->getToDate());
    }

    //Incoming Query
    private function outgoingQuery(): Collection
    {
        return CallSummaryOutgoingQuery::IOSWiseOutgoing($this->getFromDate(), $this->getToDate());
    }

    //Data setter
    private function dataSetter() {
        $sheet = $this->getWorksheetType();
        $schema = $this->schema();

        if($this->getDirection() == 'Incoming') {
            //echo 'Incoming';
            $query = $this->incomingQuery();
        } else {
            //echo 'Outgoing';
            $query = $this->outgoingQuery();
        }
        //echo "<pre>";

        $j = 0;

        foreach($query as $data) {
            $count = 0;
            $indexName = 66; //66 is B index ascii value
            //Serial
            $sheet->getActiveSheet()->setCellValue('A'.($this->reportStartCellIndex+$j), ($j+1));
            $count = $count+$j;
            for($i = 0; $i < count($schema); $i++) {

                $name = $schema[$i];

                $cellCoordinate = chr($indexName).($this->reportStartCellIndex+$j);
                $lastIndexCoordinate = $cellCoordinate;
                $sheet->getActiveSheet()
                        ->setCellValue($cellCoordinate, $data->$name)
                        ->getStyle($cellCoordinate)
                        ->getNumberFormat()
                        ->applyFromArray($this->formatNumber($name != 'ACD' ? NumberFormat::FORMAT_NUMBER_COMMA : NumberFormat::FORMAT_NUMBER_00));

                $indexName++;
            }

            $j++;

        }
        //dd($lastIndexCoordinate);
        //Wrap table section
        $tableWrapIndex = 'A'.$this->reportStartCellIndex.':'.$lastIndexCoordinate;
        $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->fontStyle());
        $sheet->getActiveSheet()->getStyle($tableWrapIndex)->applyFromArray($this->table());

        if($count == (count($query)-1)) {
            $totalCoordinate = (count($query)+$this->reportStartCellIndex);
            $sheet->getActiveSheet()->setCellValue('A'.$totalCoordinate, 'Total:')
                                                ->getStyle('A'.$totalCoordinate.':F'.$totalCoordinate)
                                                ->applyFromArray($this->footer());

            $totalCalls = '=SUM(D'.$this->reportStartCellIndex.':D'.($totalCoordinate-1).')';
            $totalMins  = '=SUM(E'.$this->reportStartCellIndex.':E'.($totalCoordinate-1).')';
            $acd        = '=E'.$totalCoordinate.'/D'.$totalCoordinate;

            $sheet->getActiveSheet()->setCellValue('D'.$totalCoordinate, $totalCalls)->getStyle('D'.$totalCoordinate)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));
            $sheet->getActiveSheet()->setCellValue('E'.$totalCoordinate, $totalMins)->getStyle('E'.$totalCoordinate)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));
            $sheet->getActiveSheet()->setCellValue('F'.$totalCoordinate, $acd)->getStyle('F'.$totalCoordinate)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_00));
        }


        return $sheet;
    }

    /**
     * @return $this
     */
    private function getReports()
    {
        $this->getDefaultWorksheet(); //Get worksheet title and default worksheet
        $this->indexAutoSize('A','F'); //Index auto size
        $this->headingSetup(); //top heading
        $this->createTableHeader(); //Create report table header
        $this->dataSetter(); //Data setting
        return $this;
    }
    // Function to get all the dates in given range

    /**
     * @param $start
     * @param $end
     * @param string $format
     * @return array
     */
    private function getDatesFromRange($start, $end, string $format = 'd-m-Y'): array
    {
        $from = Carbon::parse($start);
        $to = Carbon::parse($end);

        // Declare an empty array
        $dates = array();

        for($d = $from; $d->lte($to); $d->addDay()) {
            $dates[] = $d->format($format);
        }

        // Return the array elements
        return $dates;
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
    public function incoming($inputFromDate, $inputToDate, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->incomingExcel); //Authors
        $this->setDirection('Incoming'); //Direction or Type
        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date
        $this->setWorksheetTitle('IOS Incoming'); //Worksheet title or name
        $this->setWorksheetType($this->incomingExcel); //Incoming excel
        $this->getReports(); //get reports
        //Default Active Worksheet 0
        $this->incomingExcel->setActiveSheetIndex(0);
        $filename ='IOS wise incoming '. Carbon::parse($inputToDate)->format('d-M-Y');
        $writer = new Xlsx($this->incomingExcel);

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/ioswise/'.$filename.'.xlsx');
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
    public function outgoing($inputFromDate, $inputToDate, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->outgoingExcel); //Authors
        $this->setDirection('Outgoing'); //Direction or Type
        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date
        $this->setWorksheetTitle('IOS Outgoing'); //Worksheet title or name
        $this->setWorksheetType($this->outgoingExcel); //Incoming excel
        $this->getReports(); //get reports

        //Default Active Worksheet 0
        $this->outgoingExcel->setActiveSheetIndex(0);
        $filename ='IOS wise outgoing '. Carbon::parse($inputToDate)->format('d-M-Y');
        $writer = new Xlsx($this->outgoingExcel);
        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/ioswise/'.$filename.'.xlsx');
        }

        return true;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reports()
    {
        //dd(request()->all());
        request()->validate([
            'reportDate1'   => 'required',
            'reportDate2'   => 'required',
            'reportType'    => 'required',
            'create_file'   => 'required'
        ]);

        //dd(request()->create_file);

        $inputFromDate          = Carbon::parse(request()->reportDate1)->format('Ymd');
        $inputToDate            = Carbon::parse(request()->reportDate2)->format('Ymd');

        $processStartTime = microtime(TRUE);

        // Function call with passing the start date and end date
        $dates = $this->getDatesFromRange($inputFromDate, $inputToDate, 'Ymd');

        if(request()->create_file == 2) {
            foreach($dates as $date) {
                $fromDate = $date.' 00:00:00';
                $toDate = $date.' 23:59:59';

                if(request()->reportType == 1) {
                    $this->incoming($fromDate, $toDate);
                } elseif(request()->reportType == 2) {
                    $this->outgoing($fromDate, $toDate);
                } else {
                    $this->incoming($fromDate, $toDate);
                    $this->outgoing($fromDate, $toDate);
                }
            }
        } else {
            if(request()->reportType == 1) {
                $this->incoming($inputFromDate, $inputToDate);
            } elseif(request()->reportType == 2) {
                $this->outgoing($inputFromDate, $inputToDate);
            } else {
                $this->incoming($inputFromDate, $inputToDate);
                $this->outgoing($inputFromDate, $inputToDate);
            }
        }

        //Disconnect Worksheets from memory
        $this->incomingExcel->disconnectWorksheets();
        $this->outgoingExcel->disconnectWorksheets();
        unset($this->incomingExcel);
        unset($this->outgoingExcel);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        return redirect('platform/igw/report/ioswise')->with('success',"Report generated! Process execution time: $executionTime Seconds");
    }

    public function index() {
        $getFiles = Storage::disk('public')->files('platform/igw/ioswise');

        $files = array();

        foreach ($getFiles as $file) {
            $fileData = explode("/", $file);
            array_push($files, end($fileData));
        }

        return view('platform.igw.ioswise.index', compact('files'));
    }

    //Download IOS Daily Comparison Report
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igw/ioswise/'.$filename;
        return response()->download($file);
    }

    //Delete Generated Report
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igw/ioswise/'.$filename);
        return Redirect::to('platform/igw/report/ioswise')->with('success','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'IOS Wise '. Carbon::now()->subdays(1)->format('d-M-Y');
        $zip_file =  public_path(). '/platform/igw/ZipFiles/ioswise/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igw/ioswise/';

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $flag = 0;
        foreach ($files as $name => $file) {
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

    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igw/ioswise'));
        if($clean1) {
            return Redirect::to('platform/igw/report/ioswise')->with('success','All Reports Successfully Deleted');
        } else {
            return Redirect::to('platform/igw/report/ioswise')->with('danger','There a problem to delete files');
        }
    }
}
