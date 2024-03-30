<?php

namespace App\Http\Controllers\ICX;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Chart\GridLines;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Axis;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Query\BanglaicxReportQuery;
use App\Authors\AuthorInformation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use Illuminate\Support\Facades\DB;

class BanglaicxReportController extends Controller
{
    private $getQuery;
    private $incomingExcel;
    private $outgoingExcel;
    private $nationalExcel;
    private $screenshotExcel;
    private $reportDirection;
    private $fromDate;
    private $toDate;
    private $trafficDirection;
    private $reportName;
    private $reportDate;
    private $worksheetType;
    private $titles;
    private $reportHeaderCoordinate = 7;
    private $reportStartCoordinate = 8;
    private $subDays = 30;
    private $eachQueryResultCount;
    private $eachSchemaCount;
    private $storeTotalQueryResult;

    public function __construct()
    {
       $this->incomingExcel     = new Spreadsheet();
       $this->outgoingExcel     = new Spreadsheet();
       $this->nationalExcel     = new Spreadsheet();
       $this->screenshotExcel   = new Spreadsheet();
       $this->getQuery          = new BanglaicxReportQuery();

    }

    //Set Border and text bold style

    /**
     * @param bool $bold
     * @param int $size
     * @param string $colorCode
     * @return array[]
     */
    private function fontStyle(bool $bold=false, int $size=10, string $colorCode='000000'): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => $bold,
                'size' => $size,
                'color' => [
                'rgb' => $colorCode
                ]
            ]
        ];
    }

    //background Color Design

    /**
     * @param $colorCode
     * @return array[]
     */
    private function background($colorCode): array
    {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => $colorCode,
                ],
            ]
        ];
    }

    //Data contains table style

    /**
     * @return array[][]
     */
    private function allBorders(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ];
    }

    //Text Color Design


    /**
     * @return array[]
     */
    public function thinAndMediumOutlineBorder(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
        ];
    }

    //Text alignment

    /**
     * @param $horizontalAlign
     * @return array[]
     */
    private function alignment($horizontalAlign): array
    {
        //'horizontal' => Alignment::VERTICAL_CENTER
        return [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => $horizontalAlign,
            ]
        ];
    }

    //Text Vertical alignment

    /**
     * @return array[]
     */
    private function vAlignment(): array
    {
        //'horizontal' => Alignment::VERTICAL_CENTER
        return [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
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
                'name' => 'Times New Roman',
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

    /**
     * @param $sheet
     * @return void
     */
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

    //Duplicate word remove from string
    private function duplicateRemove(string $removeString, string $string)
    {
        return str_replace($removeString,"", $string);
    }

    //Remove multiple whitespace from string
    private function cleanString(string $string)
    {
        return preg_replace('/\s\s+/', ' ', $string);
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

    //Set traffic direction 1-incoming or 2-outgoing or 3-national
    public function setTrafficDirection(int $trafficDirection)
    {
        $this->trafficDirection = $trafficDirection;
    }

    //Get traffic direction 1-incoming or 2-outgoing or 3-national
    public function getTrafficDirection()
    {
        return $this->trafficDirection;
    }

    //Set report direction incoming or outgoing or national title
    public function setReportDirectionTitle(string $reportDirection)
    {
        $this->reportDirection = ucfirst($reportDirection);
    }

    //Get report direction incoming or outgoing or national title
    public function getReportDirectionTitle()
    {
        return $this->reportDirection;
    }

    //Set Worksheet Type (Incoming or Outgoing or Domestic)
    public function setWorksheetType($type)
    {
        $this->worksheetType = $type;
    }

    //Get Worksheet Type (Incoming or Outgoing or Domestic)
    public function getWorksheetType()
    {
        return $this->worksheetType;
    }

    //Set report name
    private function setReportName(string $name)
    {
        $this->reportName = $name;
    }

    //Get report name
    private function getReportName()
    {
        return $this->reportName;
    }

    //Set report date
    private function setReportDate($date)
    {
        $this->reportDate = $date;
    }

    //Get report date
    public function getReportDate()
    {
        return $this->reportDate;
    }

    //Report basic information

    /**
     * @return string[]
     */
    private function basicReportDetails(): array
    {
        $reportDirectionTitle = ($this->getReportDirectionTitle() == 'Incoming' || $this->getReportDirectionTitle() == 'Outgoing') ? 'International '.$this->getReportDirectionTitle().' Traffic Summary' : $this->getReportDirectionTitle().' Traffic Summary';
        return [
            '1' => $reportDirectionTitle,
            '2' => 'Reporting ICX: BanglaICX Limited',
            '3' => 'Report Date: '.$this->getReportDate(),
            '4' => 'Report Name: '.$this->getReportName()
        ];
    }

    //Set worksheet name titles, no set method declare for this method

    /**
     * @return string[][]
     */
    private function getWorksheetTitles(): array
    {
        $this->titles = array(
                            '1' => array('BT vs Novo', 'IOS Wise', 'ANS Wise', 'IOS-ANS', 'Hourly Analysis', 'Date Wise', 'Date Wise Chart'),
                            '2' => array('ANS Wise', 'IOS Wise', 'ANS-IOS', 'Hourly Analysis', 'ANS-IOS-Destination', 'Date Wise', 'Date Wise Chart'),
                            '3' => array('OriginationANS', 'TerminationANS', 'OrgANS-TermANS', 'Hourly Analysis', 'Date Wise', 'Date Wise Chart'),
                        );

        return $this->titles;
    }

    /**
     * @param array $arr
     * @return mixed
     */
    private function setBasicInformation(Array $arr)
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)

        //Set information
        foreach($arr as $key => $info) {
            $cells = 'A'.$key.':D'.$key;
            $fontStyle = $key == 1 ? $this->fontStyle(true) : $this->fontStyle();
            $sheet->getActiveSheet()->setCellValue('A'.$key, $info)->mergeCells($cells)->getStyle($cells)->applyFromArray($fontStyle); //Cell index wise info setting
        }

        return $sheet;
    }

    //Create Worksheet with basic information
    private function createSheets()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)

        //Request wise direction checking and return report direction titles
        $worksheetName  = $this->checkRequestDirection($this->getWorksheetTitles()); //Get worksheet name title

        //Report date
        $fromAndToDate = Carbon::parse($this->getToDate())->subDays($this->subDays)->format('d-M-Y').' To '. Carbon::parse($this->getToDate())->format('d-M-Y');
        $onlyToDate = Carbon::parse($this->getToDate())->format('d-M-Y');

        //Create worksheet
        for($i = 0; $i < count($worksheetName); $i++) {
            $sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $sheet->getDefaultStyle()->getFont()->setSize(10);

            if($i == 0) {
                $sheet->setActiveSheetIndex(0); //Default active worksheet.
                $sheet->getActiveSheet()->setTitle($worksheetName[$i]); //Set default worksheet name title

                //This report name set below the basic information
                $worksheetName[$i] == 'BT vs Novo' ? $this->setReportName($this->cleanString($this->duplicateRemove("Wise", $worksheetName[$i]).' day wise summary')) : $this->setReportName($this->cleanString($this->duplicateRemove("Wise", $worksheetName[$i]).' wise summary'));

                //This report name set below the basic information
                $worksheetName[$i] == 'BT vs Novo' ? $this->setReportDate($fromAndToDate) : $this->setReportDate($onlyToDate);

                //Set information
                $this->setBasicInformation($this->basicReportDetails());

            } else {
                $sheet->createSheet()->setTitle($worksheetName[$i]); //Worksheet Create

                if($worksheetName[$i] != 'Date Wise Chart') {
                    $sheet->setActiveSheetIndex($i); //Get Active worksheet

                    //This report name set below the basic information
                    $worksheetName[$i] != 'Hourly Analysis' ? $this->setReportName($this->cleanString($this->duplicateRemove("Wise", $worksheetName[$i]).' wise summary')) : $this->setReportName('Hourly');

                    //Report date
                    $worksheetName[$i] == 'Date Wise' ? $this->setReportDate($fromAndToDate) : $this->setReportDate($onlyToDate);

                    //Set information
                    $this->setBasicInformation($this->basicReportDetails());

                } else {
                    continue; //If you find 'DateWise Chart' in worksheet titles array, and ignore it
                }

            }
        }

        return $sheet;
    }

    //Checking traffic direction and report direction title
    private function checkRequestDirection($arr = null)
    {
        //Checking traffic direction and report direction title
        if($this->getTrafficDirection() == 1 && $this->getReportDirectionTitle() == ucfirst('Incoming')) {
            return $arr[$this->getTrafficDirection()]; //Return incoming worksheet name
        } elseif($this->getTrafficDirection() == 2 && $this->getReportDirectionTitle() == ucfirst('Outgoing')) {
            return $arr[$this->getTrafficDirection()]; //Return outgoing worksheet name
        } else {
            return $arr[$this->getTrafficDirection()]; //Return national or domestic worksheet name
        }
    }

    //Report header titles

    /**
     * @return array[]
     */
    public function reportHeaderTitles(): array
    {
        $CDRDetails = array('No of Billable calls','Duration in Min','ACD');
        $percentage = array('% of Total Calls','% of Total Dur');

        $btracVsNovo    = array('Sl No','Date','Bangla Trac','NovoTel');
        $iosWise        = array_merge(array('Sl No','IOS Name'), $CDRDetails, $percentage);
        $ansWise        = array_merge(array('Sl No','ANS Name'), $CDRDetails, $percentage);
        $iosAns         = array_merge(array('Sl No','IOS Name','ANS Name'), $CDRDetails); //Incoming
        $ansIos         = array_merge(array('Sl No','ANS Name','IOS Name'), $CDRDetails); //Outgoing
        $hourly         = array_merge(array('Sl No','Time'), $CDRDetails);
        $dayWise        = array_merge(array('Sl No','Date'), $CDRDetails);
        $destinationWise = array('Sl No','ANS Name','IOS Name','Destination Name','Terminating Prefix','No of Billable calls','CallDuration(Min)','Bill Duration(Min)');

        //Incoming
        $incomingReportHeader = array($btracVsNovo, $iosWise, $ansWise,$iosAns,$hourly,$dayWise);

        //Outgoing
        $outgoingReportHeader = array($ansWise,$iosWise,$ansIos,$hourly,$destinationWise,$dayWise);

        //National
        $nationalReportHeader = array(
            array_merge(array('Sl No','Originating ANS'), $CDRDetails, $percentage),
            array_merge(array('Sl No','Termination ANS'), $CDRDetails, $percentage),
            array_merge(array('Sl No','Origination ANS','Termination ANS'),$CDRDetails),
            $hourly,
            $dayWise
        );

        return ['1'=> $incomingReportHeader, '2'=> $outgoingReportHeader, '3'=>$nationalReportHeader];
    }

    //Index auto resize

    /**
     * @param $start
     * @param $end
     * @return bool
     */
    private function indexAutoSize($start, $end): bool
    {
        $sheet = $this->getWorksheetType();

        //Process auto resize index
        foreach(range($start, $end) as $index) {
            $sheet->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        return true;
    }

    //Create report header
    private function createReportHeader()
    {
        //Init
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)

        //Request wise direction checking and return array values
        $getHeaders    = $this->checkRequestDirection($this->reportHeaderTitles()); //Get header titles

        //i = 65, 65 is a character 'A' ascii value
        foreach($getHeaders as $key => $headers) {
            $sheet->setActiveSheetIndex($key);

            $rightAlignContent = array('Bangla Trac','NovoTel','No of Billable calls','Duration in Min','ACD','% of Total Calls','% of Total Dur', 'No of Billable calls','CallDuration(Min)','Bill Duration(Min)');

            $centerAlignContent = array('Date','Time');
            for($i = 0; $i < count($headers); $i++) {
                $lastCellIndex = (65+$i); //Getting each last index ascii value

                $coordinate = chr(65+$i).$this->reportHeaderCoordinate;

                if(in_array($headers[$i], $rightAlignContent)) {
                    //Setup headers in each request traffic direction
                    $sheet->getActiveSheet()->setCellValue($coordinate, $headers[$i])->getStyle($coordinate)->applyFromArray($this->alignment(Alignment::HORIZONTAL_RIGHT));
                } else {
                    if(in_array($headers[$i], $centerAlignContent)) {
                        //Setup headers in each request traffic direction
                        $sheet->getActiveSheet()->setCellValue($coordinate, $headers[$i])->getStyle($coordinate)->applyFromArray($this->alignment(Alignment::VERTICAL_CENTER));
                    } else {
                        //Setup headers in each request traffic direction
                        $sheet->getActiveSheet()->setCellValue($coordinate, $headers[$i]);
                    }

                }

            }

            //Index auto resize, convert ascii value to character
            $this->indexAutoSize(chr(65), chr($lastCellIndex)); //Index auto resize

            //Header design
            $cells = chr(65).$this->reportHeaderCoordinate.':'.chr($lastCellIndex).$this->reportHeaderCoordinate;
            $designArray = array_merge($this->fontStyle(true), $this->background('c2e6ff'), $this->allBorders()); //Set Thin border
            //$designArray = array_merge($this->fontStyle(true), $this->background('c2e6ff'), $this->thinAndMediumOutlineBorder()); //Set inside Thin and outline Thick border
            $sheet->getActiveSheet()->getStyle($cells)->applyFromArray($designArray);

        }

        return $sheet;
    }

    //DB Query container

    /**
     * @return array[]
     */
    private function queries(): array
    {
        //Previous days, it's pre-define value in $this->subDays
        $previousDays = Carbon::parse($this->getToDate())->startOfDay()->subDays($this->subDays)->format('Ymd H:i:s');
        //Report from date
        $fromDate = Carbon::parse($this->getFromDate())->startOfDay()->format('Ymd H:i:s');
        //Report to date
        $toDate = Carbon::parse($this->getToDate())->endOfDay()->format('Ymd H:i:s');

        /**
         * DB query manipulation section
         */
        $btracVsNovo = $this->getQuery->btracVsNovoDayWiseQuery($previousDays, $toDate, $this->getTrafficDirection()); //Btrac vs Novo date wise query
        $origination = $this->getQuery->originationQuery($fromDate, $toDate, $this->getTrafficDirection()); //Origination query
        $termination = $this->getQuery->terminationQuery($fromDate, $toDate, $this->getTrafficDirection()); //Termination query
        $origAndTerm = $this->getQuery->originationAndTerminationQuery($fromDate, $toDate, $this->getTrafficDirection()); //Origination and Termination query
        $hourly      = $this->getQuery->hourlyQuery($fromDate, $toDate, $this->getTrafficDirection()); //Hourly query
        $dayWise     = $this->getQuery->dayWiseQuery($previousDays, $toDate, $this->getTrafficDirection()); //Day wise query

        //Destination wise query
        $destination = $this->getQuery->destinationQuery($fromDate, $toDate);

        /**
         * Report direction wise query container
         */
        //Incoming query container
        $incoming = array($btracVsNovo, $origination, $termination,$origAndTerm,$hourly,$dayWise);

        //Outgoing query container
        $outgoing = array($origination, $termination, $origAndTerm,$hourly, $destination, $dayWise);

        //National query container
        $national = array($origination, $termination, $origAndTerm,$hourly, $dayWise);

        return ['1'=> $incoming, '2'=> $outgoing, '3'=> $national];
    }

    //DB schema container

    /**
     * @return array[]
     */
    private function schema(): array
    {
        //Common schema fields and it's distributed
        $common = array('SuccessfulCall','Duration','ACD');
        //Merge schema
        $schema1 = array_merge(array('ShortName'),$common, array('successfulCallsPercent','totalDurationPercent'));
        $schema2 = array_merge(array('TrafficDate'), $common);

        /**
         * Schema manipulation section
         */
        $btracVsNovoSchema  = array('TrafficDate','btracMin','novoMin');  //Btrac vs Novo schema
        $originationSchema  = $schema1; //Origination schema
        $termination        = $schema1; //Termination schema
        $originationAndTerminationSchema = array_merge(array('ShortNameOne','ShortNameTwo'), $common); //Origination - Termination schema
        $hourlySchema       = $schema2; //Hourly schema
        $dayWiseSchema      = $schema2; //Day wise schema
        $destinationSchema  = array('ansName','igwName','destinationName','inRatedPrefix', 'SuccessfulCall', 'Duration', 'BillDuration'); //OG destination schema

        //Incoming schema container
        $incoming = array($btracVsNovoSchema,$originationSchema,$termination, $originationAndTerminationSchema, $hourlySchema, $dayWiseSchema);
        //Outgoing schema container
        $outgoing = array($termination, $originationSchema, $originationAndTerminationSchema, $hourlySchema,$destinationSchema,$dayWiseSchema);
        //National schema container
        $national = array($originationSchema,$termination, $originationAndTerminationSchema, $hourlySchema, $dayWiseSchema);

        return ['1'=>$incoming, '2'=>$outgoing, '3'=>$national];
    }

    //Set each query for total result count
    private function setQueryResult($query)
    {
        $this->eachQueryResultCount = count($query);
    }

    //Get total query result
    private function getQueryResult()
    {
       return $this->eachQueryResultCount;
    }

    //Set schema for count
    private function setSchemaForCount($schema)
    {
       $this->eachSchemaCount = $schema;
    }

    //Get schema
    private function getSchemaTotal()
    {
        return $this->eachSchemaCount;
    }

    //Data setting in all worksheets (Incoming and Outgoing and National)
    private function dataSetter()
    {
        //Init
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)
        $queries = $this->checkRequestDirection($this->queries()); //Get direction wise queries
        $schema = $this->checkRequestDirection($this->schema()); //Get direction wise schema

        $fridaySheet = array('BT vs Novo','Date Wise'); //Date wise report
        $cdrFieldSchema = array('SuccessfulCall','Duration','ACD','successfulCallsPercent','totalDurationPercent');
        $summationWorksheet = array('IOS Wise','ANS Wise','IOS-ANS','Hourly Analysis','ANS-IOS','OriginationANS','TerminationANS','OrgANS-TermANS', 'ANS-IOS-Destination');
        $summationSchema = $cdrFieldSchema;
        $findDay = array('Friday'); //Find day in the array
        $timeFormat = array('12 AM','01 AM','02 AM','03 AM','04 AM','05 AM','06 AM','07 AM','08 AM','09 AM','10 AM','11 AM','12 PM','01 PM','02 PM','03 PM','04 PM','05 PM','06 PM','07 PM','08 PM','09 PM','10 PM','11 PM');
        $storeQueryResult = array();
        //Set right alignment db schema
        $rightAlignContent = array_merge(array('btracMin','novoMin'), $cdrFieldSchema);

        //Process db queries
        for($i = 0; $i < count($queries); $i++) {
            $sheet->setActiveSheetIndex($i); //Active worksheet

            //$this->setQueryResult(count($queries[$i])); //Each query total result count
            $this->setQueryResult($queries[$i]); //Each query total result count
            $this->setSchemaForCount(count($schema[$i])); //Each db schema count

            //Store total query count value
            array_push($storeQueryResult, $this->getQueryResult());

            //Summation cell init (successful call, duration, acd, bill duration)
            $sumFirstCell = array(); //first cell
            $sumLastCell = array(); //last cell
            $summation = array(); //Summation


            //Get each query by $i value and process query data
            foreach($queries[$i] as $key => $query) {
                $cellName = 66; //66 is B ascii value

                //Serial number echo in every active worksheet
                $sheet->getActiveSheet()->setCellValue(chr(65).($this->reportStartCoordinate+$key),($key+1)); // 65 is A Ascii value

                //Set Horizontally data in each worksheet, depends on query data and db schema
                for($j = 0; $j < count($schema[$i]); $j++) {
                    $schemaTitle = trim($schema[$i][$j]); //Get db schema

                    //Cell index and coordinate
                    $cellIndexAndCoordinate = chr($cellName+$j).($this->reportStartCoordinate+$key);

                    //Check traffic date
                    if($schemaTitle == 'TrafficDate' && in_array($sheet->getActiveSheet()->getTitle(), $fridaySheet)) {
                        //Set Traffic Date
                        $getDay = Carbon::parse($query->$schemaTitle)->format('l'); //Get Day name

                        //If it has friday in report
                        if(in_array($getDay, $findDay)) {
                            //echo $query->$schemaTitle.'--'.$getDay.'<br>';
                            $cells = chr(65).($this->reportStartCoordinate+$key).':'.chr(65+count($schema[$i])).($this->reportStartCoordinate+$key);

                            //Only friday data set and style with red mark
                            $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cells)->applyFromArray(array_merge($this->alignment(Alignment::HORIZONTAL_RIGHT),$this->fontStyle(false,10, 'FF0000'))); //Cell coordinate wise data set
                        } else {
                            //Others day (None friday) data set
                            $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->applyFromArray(array_merge($this->alignment(Alignment::HORIZONTAL_RIGHT), $this->fontStyle())); //Cell coordinate wise data set
                        }

                    } else {
                        //Check alignment array
                        if(in_array($schemaTitle,$rightAlignContent)) {
                            //Set right alignment text
                            if($schemaTitle == 'btracMin' || $schemaTitle == 'novoMin' || $schemaTitle == 'SuccessfulCall' || $schemaTitle == 'Duration') {
                                //Set 'SuccessfulCall' and 'Duration' data
                                $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->getNumberFormat()->applyFromArray(array_merge($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA), $this->alignment(Alignment::HORIZONTAL_RIGHT))); //Cell coordinate wise data set
                            } elseif($schemaTitle == 'ACD') {
                                //Set ACD data
                                $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->getNumberFormat()->applyFromArray(array_merge($this->formatNumber(NumberFormat::FORMAT_NUMBER_00), $this->alignment(Alignment::HORIZONTAL_RIGHT))); //Cell coordinate wise data set
                            } else {
                                //'successfulCallsPercent','totalDurationPercent' data set
                                $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->getNumberFormat()->applyFromArray(array_merge($this->formatNumber(NumberFormat::FORMAT_PERCENTAGE), $this->alignment(Alignment::HORIZONTAL_RIGHT))); //Cell coordinate wise data set
                            }

                        } else {

                            if($schemaTitle == 'inRatedPrefix') {
                                //Set inRatePrefix data, probably in Outgoing report destination worksheet
                                $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->applyFromArray($this->alignment(Alignment::HORIZONTAL_LEFT)); //Cell coordinate wise data set
                            }elseif($schemaTitle == 'BillDuration'){
                                $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle)->getStyle($cellIndexAndCoordinate)->getNumberFormat()->applyFromArray(array_merge($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA), $this->alignment(Alignment::HORIZONTAL_RIGHT)));
                            } else {
                                //Checking hourly worksheet and hourly data
                                if(($sheet->getActiveSheet()->getTitle() == 'Hourly Analysis') && ($schemaTitle == 'TrafficDate')){
                                    //echo $query->$schemaTitle.' ==> '.$timeFormat[$key].'<br>';
                                    $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $timeFormat[$key])->getStyle($cellIndexAndCoordinate)->applyFromArray($this->alignment(Alignment::HORIZONTAL_RIGHT)); ;
                                } else {
                                    //Set normal data
                                    $sheet->getActiveSheet()->setCellValue($cellIndexAndCoordinate, $query->$schemaTitle); //Cell coordinate wise data set
                                }
                            }
                        }
                    }

                    //Checking worksheet, if required summation
                    if (in_array($schemaTitle, $summationSchema) && in_array($sheet->getActiveSheet()->getTitle(), $summationWorksheet)) {
                        //echo $sheet->getActiveSheet()->getTitle().'-';
                        //echo chr($cellName+$j).$this->reportStartCoordinate.','.chr(66+$j).($this->getQueryResult()+$this->reportHeaderCoordinate).'<br>';

                        /**
                         * Store summation cell in an array with garbage value
                         */
                        array_push($sumFirstCell, chr($cellName+$j).$this->reportStartCoordinate); //Store first cells
                        array_push($sumLastCell, chr($cellName+$j).($this->getQueryResult()+$this->reportHeaderCoordinate)); //Store last cells
                        array_push($summation, chr($cellName+$j).($this->getQueryResult()+($this->reportHeaderCoordinate+1))); //Store summation cells
                    }
                }
            }

            /**
             * Set border in each report
             */
            $cells = chr(65).$this->reportStartCoordinate.':'.chr(65+$this->getSchemaTotal()).($this->getQueryResult()+$this->reportHeaderCoordinate);
            $borders = $this->allBorders(); //Set Thin border
            //$borders = $this->thinAndMediumOutlineBorder();  //Set inside Thin and outline Thick border
            $sheet->getActiveSheet()->getStyle($cells)->applyFromArray(array_merge($borders, $this->vAlignment()));

            /**
             * Summation section
             */

            //$sheet->getActiveSheet()->setCellValue(chr(65).($this->getQueryResult()+$this->reportStartCoordinate), 'Total');
            if(!empty($sumFirstCell) && !empty($sumLastCell) && !empty($summation)) {
                //Remove garbage values, previously stored. And Get only unique value. Merge all arrays.
                $sumArrayList = array_merge(array_unique($sumFirstCell), array_unique($sumLastCell), array_unique($summation));

                sort($sumArrayList); //Standard sorting
                natsort($sumArrayList); //Natural sorting

                $cellValueArr = array();
                foreach($sumArrayList as $key => $value) {
                    array_push($cellValueArr, $value);
                }

                //Summation cells grouping
                $cellArr = array_chunk($cellValueArr, 3);

                //Total text cell index and coordinate
                $totalTextCellIndex = chr(65).($this->getQueryResult()+$this->reportStartCoordinate);
                //Set Total text
                $sheet->getActiveSheet()->setCellValue($totalTextCellIndex, 'Total');

                $getCellLastCoordinate = null;

                //Create summation matrix
                foreach($cellArr as $key => $value) {
                    //$cellRange = '=sum('.$value[0].':'.$value[1].')';
                    $getCellLastCoordinate = $value[2];
                    if($key != 2) {
                        $cellRange = '=sum('.$value[0].':'.$value[1].')';
                        $sheet->getActiveSheet()->setCellValue($value[2], $cellRange)->getStyle($value[2])->getNumberFormat()->applyFromArray(array_merge($this->formatNumber(($key > 2 ) ? NumberFormat::FORMAT_PERCENTAGE : NumberFormat::FORMAT_NUMBER_COMMA), $this->alignment(Alignment::HORIZONTAL_RIGHT))); //Successful call summation
                    } else {
                        /**
                         * ord() convert Letter to ascii value
                         * chr() convert ascii value to letter
                         */
                        //$cellRange = '=sum('.$value[0].':'.$value[1].')';
                        //$cellRange = '=AVERAGE('.$value[0].':'.$value[1].')';
                        $getFirstLetter     = substr($value[2],0,1); //Get first letter
                        $getLastNumericValue = preg_replace('/[^0-9]/', '', $value[2]); //Get only digits from a string
                        $cellRange = '='.chr((ord($getFirstLetter)-1)).$getLastNumericValue.'/'.chr((ord($getFirstLetter)-2)).$getLastNumericValue;
                        $sheet->getActiveSheet()->setCellValue($value[2], $cellRange)->getStyle($value[2])->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_00));
                    }

                    //OG Destination worksheet BillDuration Calculation
					if(($sheet->getActiveSheet()->getTitle() == 'ANS-IOS-Destination') && ($key == 1)) {

						$cellRange = '=sum('.$value[0].':'.$value[1].')';
						/**
						 * OG destination worksheet bill duration calculation
						 * Replace 'G' with 'H' index
						 */
						//$getFirstCell   = chr(ord(substr($sumArrayList[1],0,1))+1).preg_replace('/[^0-9]/', '', $sumArrayList[1]); //Get first cell
						//$getLastCell    = chr(ord(substr($sumArrayList[3],0,1))+1).preg_replace('/[^0-9]/', '', $sumArrayList[3]); //Get last cell
						//echo '=sum('.$getFirstCell.':'.$getLastCell.') ;<br>';

						$billDurCellRange = str_replace('G','H',$cellRange); //this $billDurCellRange var echo bill duration, it's contain actually Excel cell range,like "=sum(H?:H?)"
						$billDurSumCell = str_replace('G','H', $value[2]);
						//echo $billDurCellRange.' -- '.$billDurSumCell;
						$getCellLastCoordinate = $billDurSumCell;
						$sheet->getActiveSheet()->setCellValue($billDurSumCell, $billDurCellRange)->getStyle($billDurSumCell)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));
					}
                }
                //Summation area border and font bold
                $sheet->getActiveSheet()->getStyle($totalTextCellIndex.':'.$getCellLastCoordinate)->applyFromArray(array_merge($this->allBorders(), $this->vAlignment(), $this->fontStyle(true)));
            }
        }

        //Direction wise query total result store
        $this->setTotalQueryResult($storeQueryResult);

        return $sheet;
    }

    private function setTotalQueryResult($result)
    {
        $this->storeTotalQueryResult = $result;
    }

    private function getTotalQueryResult()
    {
        return $this->storeTotalQueryResult;
    }

    /**
     * Working with Excel Chart
     */
    //Date wise worksheet bar chart
    private function dateWiseBarChartRender()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)
        $chartRender = $sheet->setActiveSheetIndexByName('Date Wise Chart');
        /* $sheet->getDefaultStyle()->applyFromArray(
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => '3958f1',
                        ],
                    ]
                ]
            );
        */

        //Direction checking
        if($this->getTrafficDirection() == 1) {
            $chart = $this->barChartRender('Int Incoming');
        } elseif($this->getTrafficDirection() == 2) {
            $chart = $this->barChartRender('Int Outgoing');
        } else {
            $chart = $this->barChartRender('National');
        }

        return $chartRender->addChart($chart); //render
    }

    //Daily Duration Chart

    /**
     * @param $titlePart
     * @return Chart
     */
    private function barChartRender($titlePart): Chart
    {
        $headerCoordinate = $this->reportHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $this->getQueryResult();

        $chartTopLeftPosition = 'C2'; //chart area starting point
        $chartBottomRightPosition = 'V'.($startingCoordinate-5); //Chart area ending point

        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate+$this->reportHeaderCoordinate;

        $seriesCategoryRange = "'Date Wise'".'!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange = "'Date Wise'".'!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING,"'Date Wise'".'!$B$7', null, 1),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange, null, 4),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange, null, 4),
        ];

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_BARCHART,              // plotType
            DataSeries::GROUPING_STANDARD,          // plotGrouping
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels,                      // plotLabel
            $xAxisTickValues,                       // plotCategory
            $dataSeriesValues                       // plotValues
        );

        $series->setPlotDirection(DataSeries::DIRECTION_COL);
        //Set the series in the plot area
        $plotArea = new PlotArea(null, [$series]);

        //Set the chart legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        $title = new Title('DateWise '.$titlePart.' Duration (Min)');
        $xAxisLabel = new Title('Date');
        $yAxisLabel = new Title('Duration');
        //  Create the chart
        $chart = new Chart (
            'chart',        // name
            $title,         // title
            //$legend,      // legend
            null,
            $plotArea,      // plotArea
            true,           // plotVisibleOnly
            //0,              // displayBlanksAs
            'zero',         // displayBlanksAs
            $xAxisLabel,    // xAxisLabel
            $yAxisLabel     // yAxisLabel
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area
        return $chart;
    }

    //Hourly chart Render
    private function hourlyChartRender()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)
        $chartRender = $sheet->setActiveSheetIndexByName('Hourly Analysis');
        //Direction checking
        if($this->getTrafficDirection() == 1) {
            $chart = $this->lineChart('Incoming Hourly Analysis');
        } elseif($this->getTrafficDirection() == 2) {
            $chart = $this->lineChart('Outgoing Hourly Analysis');
        } else {
            $chart = $this->lineChart('National Hourly Analysis');
        }

        return $chartRender->addChart($chart);
    }

    //Hourly line chart

    /**
     * @param $titlePart
     * @return Chart
     */
    private function lineChart($titlePart): Chart
    {
        $chartTopLeftPosition = 'G'.$this->reportStartCoordinate;
        $chartBottomRightPosition = 'V'.(22+$this->reportHeaderCoordinate);

        //Series Values
        $seriesValueTopPoint = $this->reportStartCoordinate;
        $seriesValueBottomPoint = 31;
        //"'Hourly Analysis'".
        //=SERIES('Hourly Analysis'!$D$7,'Hourly Analysis'!$B$8:$B$31,'Hourly Analysis'!$D$8:$D$31,2)
        //=SERIES('Hourly Analysis'!$C$7,'Hourly Analysis'!$B$8:$B$31,'Hourly Analysis'!$C$8:$C$31,1)

        $seriesCategoryRange1 = "'Hourly Analysis'".'!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesCategoryRange2 = "'Hourly Analysis'".'!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange1 = "'Hourly Analysis'".'!$C$'.$seriesValueTopPoint.':$C$'.$seriesValueBottomPoint;
        $seriesValueRange2 = "'Hourly Analysis'".'!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Hourly Analysis'".'!$C$7', null, 1), //Right x-axis
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Hourly Analysis'".'!$D$7', null, 1), //Right x-axis
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange1, null, 4),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange2, null, 4),
        ];


        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange1, null, 4),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange2, null, 4),
        ];

        $dataSeriesValues[0]->setLineWidth(30000);
        $dataSeriesValues[1]->setLineWidth(30000);

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_LINECHART, // plotType
            DataSeries::GROUPING_STACKED, // plotGrouping
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues        // plotValues
        );
        //dd($series);
        // Set the series in the plot area
        $plotArea = new PlotArea(null, [$series]);
        // Set the chart legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title($titlePart);
        //$yAxisLabel = new Title('Value');
        //$xaxis = new Axis();
        //$xaxis->setAxisOptionsProperties('low', null, null, null, null, null, 0, 0, null, null);
        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            $title,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,              // displayBlanksAs
            'zero',         // displayBlanksAs
            null,       // xAxisLabel
            //$yAxisLabel  // yAxisLabel
            null
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $chart;
    }

    //Termination pie chart
    private function terminationPieChartRender()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)

        $queryWiseTotalResult = $this->getTotalQueryResult();
        //Direction checking (1-Incoming and 2-Outgoing and 3-National)
        if($this->getTrafficDirection() == 1) {
            $chartRender = $sheet->setActiveSheetIndexByName('ANS Wise');
            $seriesLabels = "'ANS Wise'".'!$D$7';
            $categoryRange = array("'ANS Wise'".'!$B$', '$B$');
            $valueRange = array("'ANS Wise'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[2], $seriesLabels, $categoryRange, $valueRange, 'ANS Wise Dur in Percentage(%)
            ');
        } elseif($this->getTrafficDirection() == 2) {
            $chartRender = $sheet->setActiveSheetIndexByName('IOS Wise');
            $seriesLabels = "'IOS Wise'".'!$D$7';
            $categoryRange = array("'IOS Wise'".'!$B$', '$B$');
            $valueRange = array("'IOS Wise'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[1],$seriesLabels, $categoryRange, $valueRange, 'IOS Wise Duration in Percentage(%)');
        } else {
            $chartRender = $sheet->setActiveSheetIndexByName('TerminationANS');
            $seriesLabels = "'TerminationANS'".'!$D$7';
            $categoryRange = array("'TerminationANS'".'!$B$', '$B$');
            $valueRange = array("'TerminationANS'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[1],$seriesLabels, $categoryRange, $valueRange, 'Termination ANS Dur in Percentage(%)');
        }

        return $chartRender->addChart($chart);
    }

    //Origination pie chart
    private function originationPieChartRender()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)

        $queryWiseTotalResult = $this->getTotalQueryResult();
        //Direction checking (1-Incoming and 2-Outgoing and 3-National)
        if($this->getTrafficDirection() == 1) {
            $chartRender = $sheet->setActiveSheetIndexByName('IOS Wise');
            $seriesLabels = "'IOS Wise'".'!$D$7';
            $categoryRange = array("'IOS Wise'".'!$B$', '$B$');
            $valueRange = array("'IOS Wise'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[1], $seriesLabels, $categoryRange, $valueRange, 'IOS Wise Dur in Percentage(%)');
        } elseif($this->getTrafficDirection() == 2) {
            $chartRender = $sheet->setActiveSheetIndexByName('ANS Wise');
            $seriesLabels = "'ANS Wise'".'!$D$7';
            $categoryRange = array("'ANS Wise'".'!$B$', '$B$');
            $valueRange = array("'ANS Wise'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[0],$seriesLabels, $categoryRange, $valueRange, 'ANS Wise Dur in Percentage(%)');
        } else {
            $chartRender = $sheet->setActiveSheetIndexByName('OriginationANS');
            $seriesLabels = "'OriginationANS'".'!$D$7';
            $categoryRange = array("'OriginationANS'".'!$B$', '$B$');
            $valueRange = array("'OriginationANS'".'!$D$', '$D$');
            $chart = $this->pieChart($queryWiseTotalResult[0],$seriesLabels, $categoryRange, $valueRange, 'Origination ANS Dur in Percentage(%)');
        }

        return $chartRender->addChart($chart);
    }

    //Pie Chart

    /**
     * @param $queryTotal
     * @param $seriesLabels
     * @param array $categoryRange
     * @param array $valueRange
     * @param $titlePart
     * @return Chart
     */
    private function pieChart($queryTotal, $seriesLabels, $categoryRange=[], $valueRange=[], $titlePart): Chart
    {
        $chartTopLeftPosition = 'A'.(($this->reportHeaderCoordinate+$queryTotal)+6);
        $chartBottomRightPosition = 'H'.((($this->reportHeaderCoordinate+$queryTotal)+8)+16);

        //Series Values
        $seriesValueTopPoint = $this->reportStartCoordinate;
        $seriesValueBottomPoint = $this->reportHeaderCoordinate+$queryTotal;

        $seriesCategoryRange = $categoryRange[0].$seriesValueTopPoint.':'.$categoryRange[1].$seriesValueBottomPoint;
        $seriesValueRange = $valueRange[0].$seriesValueTopPoint.':'.$valueRange[1].$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesLabels, null, 1),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange, null, 4),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange, null, 4),
        ];

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_PIECHART, // plotType
            null, // plotGrouping (Pie charts don't have any grouping)
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues // plotValues
        );

        //Set up a layout object for the Pie chart
        $layout = new Layout();
        //$layout->setShowVal(true);
        $layout->setShowLegendKey(true);
        $layout->setShowCatName(true);
        $layout->setShowPercent(true);
        $layout->setShowLeaderLines(true);

        //  Set the series in the plot area
        $plotArea = new PlotArea($layout, [$series]);
        //  Set the chart legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        $title = new Title($titlePart);

        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            $title,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,              // displayBlanksAs
            'zero',         // displayBlanksAs
            null,       // xAxisLabel
            null        // yAxisLabel    - Pie charts don't have a Y-Axis
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $chart;
    }

    //BT vs Novo Chart create
    private function btVsNovoBarChartRender()
    {
        $sheet = $this->getWorksheetType(); //Get worksheet type (incoming or outgoing or national)
        $chartRender = $sheet->setActiveSheetIndexByName('BT vs Novo');
        $queryWiseTotalResult = $this->getTotalQueryResult();
        $chart = $this->standardBarChart($queryWiseTotalResult[0],'BT vs Novo');
        return $chartRender->addChart($chart);
    }

    //Standard Bar Chart

    /**
     * @param $queryTotal
     * @param $titlePart
     * @return Chart
     */
    private function standardBarChart($queryTotal, $titlePart): Chart
    {
        //private $reportHeaderCoordinate = 7;
        //private $reportStartCoordinate = 8;
        $chartTopLeftPosition = 'F'.$this->reportStartCoordinate;
        $chartBottomRightPosition = 'V24';

        //Series Values
        $seriesValueTopPoint = $this->reportStartCoordinate;
        $seriesValueBottomPoint = ($queryTotal+$this->reportHeaderCoordinate);
        //"'Hourly Analysis'".
        //=SERIES('Hourly Analysis'!$D$7,'Hourly Analysis'!$B$8:$B$31,'Hourly Analysis'!$D$8:$D$31,2)
        //=SERIES('Hourly Analysis'!$C$7,'Hourly Analysis'!$B$8:$B$31,'Hourly Analysis'!$C$8:$C$31,1)

        $seriesCategoryRange1 = "'BT vs Novo'".'!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesCategoryRange2 = "'BT vs Novo'".'!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange1 = "'BT vs Novo'".'!$C$'.$seriesValueTopPoint.':$C$'.$seriesValueBottomPoint;
        $seriesValueRange2 = "'BT vs Novo'".'!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'BT vs Novo'".'!$C$7', null, 1), //Right x-axis
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'BT vs Novo'".'!$D$7', null, 1), //Right x-axis
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange1, null, 4),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange2, null, 4),
        ];


        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange1, null, 4),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange2, null, 4),
        ];

        $dataSeriesValues[0]->setLineWidth(30000);
        $dataSeriesValues[1]->setLineWidth(30000);

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_BARCHART, // plotType
            DataSeries::GROUPING_STANDARD, // plotGrouping
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues        // plotValues
        );
        //dd($series);
        // Set the series in the plot area
        $plotArea = new PlotArea(null, [$series]);
        // Set the chart legend
        $legend = new Legend(Legend::POSITION_TOP, null, false);
        $title = new Title($titlePart);
        $xAxisLabel = new Title('Date');
        //$yAxisLabel = new Title('Duration');

        //  Create the chart
        $chart = new Chart (
            'chart',        // name
            $title,         // title
            $legend,        // legend
            $plotArea,      // plotArea
            true,           // plotVisibleOnly
            //0,              // displayBlanksAs
            'zero',         // displayBlanksAs
            $xAxisLabel,    // xAxisLabel
            null            // yAxisLabel
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $chart;
    }

    //Include all common chart

    /**
     * @return $this
     */
    private function chartRender()
    {
        $this->originationPieChartRender();
        $this->terminationPieChartRender();
        $this->hourlyChartRender();
        $this->dateWiseBarChartRender();
        return $this;
    }

    /**
     * Sticky summary data setup
     */
    public function stickyDataSetter()
    {
        $sheet = $this->getWorksheetType();
        //Report from date
        $fromDate = Carbon::parse($this->getFromDate())->startOfDay()->format('Ymd H:i:s');
        //Report to date
        $toDate = Carbon::parse($this->getToDate())->endOfDay()->format('Ymd H:i:s');

        //Summary query
        $incoming = $this->getQuery->dayWiseQuery($fromDate, $toDate, 1);
        $outgoing = $this->getQuery->dayWiseQuery($fromDate, $toDate, 2);
        $national = $this->getQuery->dayWiseQuery($fromDate, $toDate, 3);


        /**
         * Day wise total summary section
         * incoming, outgoing, national
         */

        $header1 = array('Traffic Category','No of Billable calls','Duration in Min','ACD');
        $vertical_titles = array('Int. Incoming', 'Int. Outgoing', 'National/Domestic');
        //$schema = array("SuccessfulCall","Duration","ACD");
        $summary = array($incoming, $outgoing, $national);

		//Cell Auto Size, 66 is B ascii value
        $this->indexAutoSize(chr(66), chr(66+count($header1)-1));

        //Total summary table Header
		for($i = 0; $i < count($header1); $i++) {
			$sheet->getActiveSheet()->setCellValue(chr(66+$i).$this->reportHeaderCoordinate, $header1[$i])->getStyle(chr(66+$i).$this->reportHeaderCoordinate)->applyFromArray($i != 0? $this->alignment(Alignment::HORIZONTAL_RIGHT):$this->alignment(Alignment::HORIZONTAL_LEFT));
        }
		//Call summary header design
		$cells = chr(66).$this->reportHeaderCoordinate.':'.chr(66+(count($header1)-1)).$this->reportHeaderCoordinate;
		$sheet->getActiveSheet()->getStyle($cells)->applyFromArray(array_merge($this->fontStyle(true), $this->allBorders(), $this->background('b0d9ff')));

        //Summary table day wise data setup
        for($i = 0; $i < count($summary); $i++) {
            foreach($summary[$i] as $key => $data) {
                $data = array_values(array_merge(array($vertical_titles[$i]), array_slice(get_object_vars($data),-3)));
                $indexValue = 66;
                for($j = 0; $j < count($data); $j++){
					//echo $j.', ';
                    $cells = chr($indexValue+$j).($this->reportStartCoordinate+$i);
					if($j == 0){
						$sheet->getActiveSheet()->setCellValue($cells, $data[$j])->getStyle($cells)->applyFromArray($this->alignment(Alignment::HORIZONTAL_LEFT));
					} else {
						$sheet->getActiveSheet()->setCellValue($cells, $data[$j])->getStyle($cells)->getNumberFormat()->applyFromArray($j == 3 ? $this->formatNumber(NumberFormat::FORMAT_NUMBER_00) : $this->formatNumber(NumberFormat::FORMAT_NUMBER_COMMA));
					}
                }
                $indexValue++;
            }
        }
        //Design total summary table
        $sheet->getActiveSheet()->getStyle('B8:E10')->applyFromArray(array_merge($this->fontStyle(true), $this->vAlignment(), $this->allBorders()));


        /**
         * BICX summary->percent table screenshot create
         */
        $percentage_tbl_header = array('IOS Name','% of Total Billable Calls','% of Total Dur','% of Total Billable Calls','% of Total Dur');
        //Percent query
        $percent = $this->getQuery->stickySummaryPercent($fromDate, $toDate);

        //Cell Auto Size, 71 is B ascii value
        $this->indexAutoSize(chr(71), chr(71+count($header1)-1));

        //Direction header incoming or outgoing
        $styles = array_merge($this->fontStyle(true),$this->alignment(Alignment::HORIZONTAL_CENTER), $this->allBorders());
        $sheet->getActiveSheet()->setCellValue('H6', 'Int Incoming')->mergeCells('H6:I6')->getStyle('H6:I6')->applyFromArray($styles);
        $sheet->getActiveSheet()->setCellValue('J6', 'Int outgoing')->mergeCells('J6:K6')->getStyle('J6:K6')->applyFromArray($styles);

        //Total summary table Header
		for($i = 0; $i < count($percentage_tbl_header); $i++) {
			$sheet->getActiveSheet()->setCellValue(chr(71+$i).$this->reportHeaderCoordinate, $percentage_tbl_header[$i])->getStyle(chr(71+$i).$this->reportHeaderCoordinate)->applyFromArray($i != 0? $this->alignment(Alignment::HORIZONTAL_RIGHT):$this->alignment(Alignment::HORIZONTAL_LEFT));
        }
		//Call summary header design
		$cells = chr(71).$this->reportHeaderCoordinate.':'.chr(71+(count($percentage_tbl_header)-1)).$this->reportHeaderCoordinate;
		$sheet->getActiveSheet()->getStyle($cells)->applyFromArray(array_merge($this->fontStyle(true), $this->allBorders()));

        $row = 0;
        foreach($percent as $key => $data) {
            $data = array_values(get_object_vars($data));
            $startIndex = 71; // G ascii value is 71;
            for($i = 0; $i < count($data); $i++) {
                $cells = chr($startIndex+$i).($this->reportStartCoordinate+$row);
                if($i == 0){
                    $sheet->getActiveSheet()->setCellValue($cells, $data[$i]);
                } else {
                    $sheet->getActiveSheet()->setCellValue($cells, $data[$i])->getStyle($cells)->getNumberFormat()->applyFromArray($this->formatNumber(NumberFormat::FORMAT_PERCENTAGE));
                }
            }
            $row++;
            $startIndex++;
        }

        //Design percent summary table
        $sheet->getActiveSheet()->getStyle('G8:K'.($this->reportStartCoordinate+(count($percent)-1)))->applyFromArray(array_merge($this->fontStyle(), $this->vAlignment(), $this->allBorders()));
        $sheet->getActiveSheet()->getStyle('H6:I'.($this->reportStartCoordinate+(count($percent)-1)))->applyFromArray($this->background('b0d9ff'));
        $sheet->getActiveSheet()->getStyle('J6:K'.($this->reportStartCoordinate+(count($percent)-1)))->applyFromArray($this->background('ffd8d0'));
    }

    //Incoming report
    private function incomingReport()
    {
        $this->authorInfo($this->incomingExcel); //Authors
        $this->setReportDirectionTitle('Incoming');
        $this->setTrafficDirection(1); //1 Incoming
        $this->setWorksheetType($this->incomingExcel);
        $this->createSheets();
        $this->createReportHeader();
        $this->dataSetter();
        $this->btVsNovoBarChartRender();
        $this->chartRender();

        //Default Active Worksheet 0
        $this->incomingExcel->setActiveSheetIndex(0);
        $filename = Carbon::parse($this->getToDate())->format('d M Y').' Bangla ICX Int. Incoming Traffic';
        $writer = new Xlsx($this->incomingExcel);
        $writer->setIncludeCharts(true);
        $writer->save(public_path().'/platform/icx/callsummary/'.$filename.'.xlsx');

        //Disconnect Worksheets from memory
        $this->incomingExcel->disconnectWorksheets();
        unset($this->incomingExcel);
    }

    //Outgoing report
    private function outgoingReport()
    {
        $this->authorInfo($this->outgoingExcel); //Authors
        $this->setReportDirectionTitle('Outgoing');
        $this->setTrafficDirection(2); //2 Outgoing
        $this->setWorksheetType($this->outgoingExcel);
        $this->createSheets();
        $this->createReportHeader();
        $this->dataSetter();
        $this->chartRender();

        //Default Active Worksheet 0
        $this->outgoingExcel->setActiveSheetIndex(0);
        $filename = Carbon::parse($this->getToDate())->format('d M Y').' Bangla ICX Int.Outgoing Traffic';
        $writer = new Xlsx($this->outgoingExcel);
        $writer->setIncludeCharts(true);
        $writer->save(public_path().'/platform/icx/callsummary/'.$filename.'.xlsx');

        //Disconnect Worksheets from memory
        $this->outgoingExcel->disconnectWorksheets();
        unset($this->outgoingExcel);
    }

    //National report
    private function nationalReport()
    {
        $this->authorInfo($this->nationalExcel); //Authors
        $this->setReportDirectionTitle('National');
        $this->setTrafficDirection(3); //3 National
        $this->setWorksheetType($this->nationalExcel);
        $this->createSheets();
        $this->createReportHeader();
        $this->dataSetter();
        $this->chartRender();

        //Default Active Worksheet 0
        $this->nationalExcel->setActiveSheetIndex(0);
        $filename = Carbon::parse($this->getToDate())->format('d M Y').' Bangla ICX National Traffic';
        $writer = new Xlsx($this->nationalExcel);
        $writer->setIncludeCharts(true);
        $writer->save(public_path().'/platform/icx/callsummary/'.$filename.'.xlsx');

        //Disconnect Worksheets from memory
        $this->nationalExcel->disconnectWorksheets();
        unset($this->nationalExcel);
    }

    //Sticky summary report
    private function stickySummary()
    {
        $this->authorInfo($this->screenshotExcel); //Authors
        $this->setWorksheetType($this->screenshotExcel);
        $this->stickyDataSetter();

        //Default Active Worksheet 0
        $this->screenshotExcel->setActiveSheetIndex(0);
        $filename ='Summary-BanglaICX '. Carbon::parse($this->getToDate())->format('d M Y');
        $writer = new Xlsx($this->screenshotExcel);
        $writer->save(public_path().'/platform/icx/callsummary/'.$filename.'.xlsx');

        //Disconnect Worksheets from memory
        $this->screenshotExcel->disconnectWorksheets();
        unset($this->screenshotExcel);
    }

//    public function dumpOrDie()
//    {
//        $this->setReportDirectionTitle('Incoming');
//        $this->setTrafficDirection(1); //1 Incoming
//        $this->setWorksheetType($this->incomingExcel);
//        $dd = $this->queries();
//        dump($dd[1]);
//    }

    //Index

    /**
     * @return RedirectResponse
     */
    public function reports(): RedirectResponse
    {

        request()->validate([
            'reportDate1'   => 'required',
            'reportType'    => 'required',
        ]);

        $inputFromDate          = Carbon::parse(request()->reportDate1)->format('Ymd');
        $inputToDate            = Carbon::parse(request()->reportDate1)->format('Ymd');

        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date

        //Tester
        //$this->dumpOrDie();

        $processStartTime = microtime(TRUE);

        //Generate all reports
        if(request()->reportType == 'all') {
            //dump(request()->reportType);
            $this->incomingReport();
            $this->outgoingReport();
            $this->nationalReport();
            $this->stickySummary();
        } elseif(request()->reportType == 1) {
            //Generate only incoming report
            $this->incomingReport();
        } elseif(request()->reportType == 2) {
            //Generate only Outgoing report
            $this->outgoingReport();
        } elseif(request()->reportType == 3) {
            //Generate only National report
            $this->nationalReport();
        }else {
            //Generate only sticky summary report
            $this->stickySummary();
        }

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        return Redirect::to('platform/banglaicx/report/callsummary')->with('success',"Report generated! Process execution time: $executionTime Seconds");
    }

    //Loading form with files (If file exist in the file stored directory)
    public function index() {
        $getFiles = Storage::disk('public')->files('platform/icx/callsummary/');

        $files = array();

        foreach ($getFiles as $file) {
            $fileData = explode("/", $file);
            array_push($files, end($fileData));
        }

        return view('platform.icx.index', compact('files'));
    }

    //Download IOS Daily Comparison Report

    /**
     * @param $filename
     * @return BinaryFileResponse
     */
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/icx/callsummary/'.$filename;
        return response()->download($file);
    }

    //Delete Generated Report

    /**
     * @param $filename
     * @return RedirectResponse
     */
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/icx/callsummary/'.$filename);
        return Redirect::to('platform/banglaicx/report/callsummary')->with('success','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'BanglaICX '. Carbon::now()->subdays(1)->format('d-M-Y');
        $zip_file =  public_path(). '/platform/icx/zipFiles/callsummary/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/icx/callsummary/';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
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
            return Redirect::to('platform/banglaicx/report/callsummary')->with('danger','Directory is empty. Please generate reports');
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
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/icx/callsummary/'));
        if($clean1) {
            return Redirect::to('platform/banglaicx/report/callsummary')->with('success','All Reports Successfully Deleted');
        } else {
            return Redirect::to('platform/banglaicx/report/callsummary')->with('danger','There a problem to delete files');
        }
    }
}
