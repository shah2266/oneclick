<?php

namespace App\Http\Controllers\IGWANDIOS;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Authors\AuthorInformation;
use App\Models\IofCompany;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class IofDailySummaryReportController extends Controller
{
    /**
     * Zoom value
     */

    public $zoomValue;
    public $excel;
    public $cellIndex=6;
    public $asciiA = 65; //A ascii value
    public $asciiB = 66; //B ascii value

    /**
     * Report generate from date
     * @param $date
     */
    private $fromDate;

    /**
     * Report generate To date
     * @param $date
     */
    private $toDate;


    protected $type;
    protected $connectionString;
    protected $dbTableColumn1;
    protected $dbTableColumn2;
    protected $direction;

    public function setType($t) {
        $this->type = $t;
    }

    public function getType() {
        return $this->type;
    }

    public function setConnectionString($conn) {
        $this->connectionString = $conn;
    }

    public function getConnectionString() {
        return $this->connectionString;
    }

    public function setTableColumnOne($col) {
        $this->dbTableColumn1 = $col;
    }

    public function getTableColumnOne() {
        return $this->dbTableColumn1;
    }

    public function setTableColumnTwo($col) {
        $this->dbTableColumn2 = $col;
    }

    public function getTableColumnTwo() {
        return $this->dbTableColumn2;
    }

    public function setDirection($d) {
        $this->direction = $d;
    }

    public function getDirection() {
        return $this->direction;
    }

    //Get platform from local:Iof Company table
    public function getplatform(): array
    {
        $platform = new IofCompany();
        return $platform->typeOptions();
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

    public function __construct() {
        $this->excel = new Spreadsheet();
    }

    //Set Border and text bold style
    private function fontStyle($fontName='Tohoma', $bold=false, $size=11): array
    {
        return [
            'font' => [
                    'name' => $fontName,
                    'bold' => $bold,
                    'size' => $size,
                    'color' => [
                    'rgb' => '000000'
                ]
            ]
        ];
    }

    //background Color Design
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
    private function allBorders($border=Border::BORDER_THIN): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => $border,
                ]
            ]
        ];
    }

    /**
     * @return array[][]
     */
    private function thinAndMediumOutlineBorder(): array
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

    //Outline Border
    private function outlineBorder($border=Border::BORDER_MEDIUM): array
    {
        return [
            'borders' => [
                'outline' => [
                    'borderStyle' => $border,
                ]
            ]
        ];
    }

    //Text alignment
    private function alignment($verticalAlign,$horizontalAlign, $wrapText = FALSE): array
    {
        //'horizontal' => Alignment::VERTICAL_CENTER
        return [
            'alignment' => [
                'vertical'   => $verticalAlign,
                'horizontal' => $horizontalAlign,
                'wrapText'   => $wrapText
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

    //Data contains table header style
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

    public function setZoomValue(int $value) {
        $this->zoomValue = $value;
    }

    public function getZoomValue() {
        return $this->zoomValue;
    }

    /**
     * @return string[]
     */
    private function sheetName(): array
    {
        //Worksheet name
        return ['Form-C (IOS Daily)', 'Form-E (IGW Daily)'];
    }

    /**
     * @param $vAlign
     * @param $hAlign
     * @param $cellCoordinate
     * @param $wrapText
     * @return Spreadsheet
     */
    public function setAlignment($vAlign, $hAlign, $cellCoordinate, $wrapText): Spreadsheet
    {
        //Alignment
        $this->excel->getActiveSheet()->getStyle($cellCoordinate)->applyFromArray(
            $this->alignment($vAlign, $hAlign,$wrapText)
        );

        return $this->excel;
    }


    /**
     * @param $cellCoordinate
     * @param $format
     * @param string $hAlign
     * @return Spreadsheet
     */
    private function numberFormat($cellCoordinate, $format, $hAlign = Alignment::HORIZONTAL_RIGHT): Spreadsheet
    {
        $this->excel->getActiveSheet()->getStyle($cellCoordinate)->getNumberFormat()->applyFromArray(
                array_merge(
                    $this->formatNumber($format),
                    $this->alignment(Alignment::VERTICAL_CENTER,$hAlign)
                )
            );

        return $this->excel;
    }

    /**
     * @param $indexName
     * @param $pValue
     * @return Spreadsheet
     */
    private function setWidth($indexName, $pValue): Spreadsheet
    {
        //Set width
        $this->excel->getActiveSheet()->getColumnDimension($indexName)->setWidth($pValue);
        return $this->excel;
    }

    /**
     * @param $cellIndex
     * @param $height
     * @return Spreadsheet
     */
    private function rowHeight($cellIndex, $height): Spreadsheet
    {
        //Set row height
        $this->excel->getActiveSheet()->getRowDimension($cellIndex)->setRowHeight($height);

        return $this->excel;
    }

    /**
     * @return Spreadsheet
     */
    private function zoomScale(): Spreadsheet
    {
        //Zoom scale set
        try {
            $this->excel->getActiveSheet()->getSheetView()->setZoomScale($this->getZoomValue());
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $this->excel;
    }

    /**
     * @param $cells
     * @param $styles
     * @return Spreadsheet
     */
    private function setStyle($cells, $styles): Spreadsheet
    {
        //Set Style
        $this->excel->getActiveSheet()->getStyle($cells)->applyFromArray($styles);
        return $this->excel;
    }

    /**
     * @return array
     */
    public function reportHeadingDetails(): array
    {
        //Report Heading details both Form-C and Form-E

        //Main report column heading
        $incoming   = array('Call Attempts','Successful Calls','Failed Calls','Minutes Terminated Incoming','Average Call Duration','Answer to Seizure Ratio');
        $outgoing   = array('Call Attempts','Successful Calls','Failed Calls','Minutes Terminated Outgoing(Actual Duration)','Minutes Terminated Outgoing(Billable Duration)','Average Call Duration','Answer to Seizure Ratio');
        $others     = array('Incoming call','Outgoing call(Actual Duration)','Outgoing call(Billable Duration)');


        $iosIncoming = array_merge(['Serial','IOS Name'],$incoming);
        $iosOutgoing = array_merge($outgoing,['Amount of Data Transfared','*MRTG Graph']);

        $icxIncoming = array_merge(['Serial','ICX Name'],$incoming);
        $icxOutgoing = $outgoing;

        //Form-E heading
        $iosHeading = array_merge($iosIncoming, $iosOutgoing);

        //Form-C heading
        $icxHeading = array_merge($icxIncoming, $icxOutgoing);
        $ansHeading = array_merge(['Serial','ANS Name'], $others);
        $igwHeading = array_merge(['Serial','IGW Name'], $others);

        return array('1'=>$icxHeading, '2'=>$ansHeading, '3'=>$igwHeading,'4'=>$iosHeading);
    }

    /**
     * @param $index
     * @return Spreadsheet
     * @throws Exception
     */
    private  function basicInfoSetting($index): Spreadsheet
    {
        //Basic info setting
        //Report date
        $reportDate = Carbon::parse($this->getToDate())->format('d-M-Y');
        //Report submission date
        $submissionDate = Carbon::now()->format('d-M-Y');

        $form_c = array('G1'=>'Form-C','G2'=>'IOS Daily Report','B3'=>'Traffic report of:','G3'=>'Submission Date:','B4'=>'IOS Name:','C3'=>$reportDate,'L3'=>$submissionDate,'C4'=>'Bangla Trac Communications Limited','D5'=>'Incoming Calls','J5'=>'Outgoing Calls','S5'=>'ANS Report','Y5'=>'IGW Report');
        $form_e = array('G1'=>'Form-E','G2'=>'IGW Daily Report','B3'=>'Traffic report of:','G3'=>'Submission Date:','B4'=>'IGW Name:','C3'=>$reportDate,'I3'=>$submissionDate,'C4'=>'Bangla Trac Communications Limited','D5'=>'Incoming Calls','J5'=>'Outgoing Calls');

        $boldTitle = array('G2','D5','J5','L3','I3');
        $borderMediumSingleTitle = array('B3','B4');

        $heading = array('0'=>$form_c, '1'=>$form_e);
        //Basic title
        foreach($heading[$index] as $key => $title) {
            if(in_array($key,$boldTitle)) {
                //Set all value
                $this->excel->getActiveSheet()->setCellValue($key,$title);

                //Set cell style only G2 cell
                if($key == 'G2') {
                    //Font style
                    $this->setStyle($key,$this->fontStyle('Calibri', false, 20));
                    //Background and border
                    $this->setStyle(($index == 0)?'B2:P2':'B2:R2',array_merge($this->background('daeef3'), $this->outlineBorder()));

                } else {
                    if($key == 'L3' OR $key == 'I3') {
                        //Set Styles
                        $this->setStyle($key, $this->fontStyle('Verdana', true, 12));
                        //Alignment
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_RIGHT,$key, FALSE);

                    } else {
                        if($key == 'D5' || $key == 'J5') {
                            //Merge Cells
                            $this->excel->getActiveSheet()->mergeCells(($key == 'D5') ? 'D5:I5':'J5:M5');

                            //Style cell range
                            ($index == 0)? $cells='J5:P5': $cells='J5:R5';

                            //Set Styles
                            $this->setStyle(($key == 'D5') ? 'D5:I5':$cells, array_merge($this->fontStyle('Tohoma', true),$this->background('dce6f1'), $this->outlineBorder()));

                            //Alignment
                            $this->setAlignment(Alignment::VERTICAL_CENTER,($key == 'D5') ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_RIGHT,($key == 'D5')?'D5':'J5', FALSE);
                        } else {
                            //Other Set Styles
                            $this->setStyle($key,$this->fontStyle('Tohoma', true));
                        }

                    }

                }

            } else {
                //Set all value
                $this->excel->getActiveSheet()->setCellValue($key,$title);

                //Set cell style only G1 cell
                if($key == 'G1') {
                    //Font styles
                    $this->setStyle($key,$this->fontStyle('Calibri', false, 14));
                    //Background and border
                    $this->setStyle(($index == 0)?'B1:P1':'B1:R1',array_merge($this->background('daeef3'), $this->outlineBorder()));
                }

                //Single title Medium border setting
                if(in_array($key, $borderMediumSingleTitle)) {
                    $this->setStyle($key,$this->outlineBorder());
                }

                //C3 and G3 Cells Styles
                if($key == 'C3' || $key == 'G3'){

                    if($index == 0) {
                        $this->setStyle(($key == 'C3') ? 'C3:F3':'G3:P3', array_merge( $this->fontStyle('Verdana', true, 12),$this->outlineBorder()));
                    } else {
                        $this->setStyle(($key == 'C3') ? 'C3:F3':'G3:R3', array_merge($this->fontStyle('Verdana', true, 12),$this->outlineBorder()));
                    }
                    //Text Right Alignment
                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_RIGHT,'C3', FALSE);
                }

                //C4 Cell Style
                if($key == 'C4') {

                    if($index == 0) {
                        $this->setStyle('C4:P4', $this->outlineBorder());
                    } else {
                        $this->setStyle('C4:R4', $this->outlineBorder());
                    }
                    //Text Right Alignment
                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_RIGHT,'C3', FALSE);
                }

                //S5 and Y5 Cell Styles
                if($key == 'S5' || $key == 'Y5') {
                    //Font styles
                    $this->setStyle($key,$this->fontStyle('Calibri', false, 16));
                    //Background and border
                    $this->setStyle(($key == 'S5')?'R5:V5':'X5:AB5',array_merge($this->outlineBorder()));
                }
            }
        }

        return $this->excel;
    }

    /**
     * @return Spreadsheet
     * @throws Exception
     */
    private function createSheet(): Spreadsheet
    {
        //$getSheetName = $this->sheetName();

        for($i=0; $i < count($getSheetName = $this->sheetName()); $i++) {
            //dump($getSheetName[$i]);

            //$this->excel->setActiveSheetIndex($sheetNumber)->setShowGridlines(false);

            if($i==0){

                $this->excel->setActiveSheetIndex(0); //Default active worksheet.
                $this->excel->getActiveSheet()->setTitle($getSheetName[$i]); //Set default worksheet name title

                //Zoom scale
                $this->zoomScale();

                //Set Height
                $this->rowHeight($this->cellIndex, 42); // Set row height

                //Set row height
                $this->basicInfoSetting(0);

                //Getting report main area heading
                $getHeading = $this->reportHeadingDetails();

                //Form-C Report heading
                foreach($getHeading as $key => $heading){

                    if($key < 4){

                        $k = 0;
                        for($j=0; $j < count($heading); $j++) {
                            //dump(chr($this->asciiB+$j).$this->cellIndex);
                            if($key == 1){
                                //ICX heading
                                $indexName = chr($this->asciiB+$j);
                                $this->excel->getActiveSheet()->setCellValue($indexName.$this->cellIndex,$heading[$j]);
                                if($j > 4 && $j < 11) {
                                    $this->setWidth($indexName, 16);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);

                                } elseif($j >= 11) {
                                    $this->setWidth($indexName, 30);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);

                                } else {
                                    $this->excel->getActiveSheet()->getColumnDimension($indexName)->setAutoSize(true);
                                    if($j > 1) {
                                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                                    } else {
                                        if($j == 1) {
                                            //Font styles
                                            $this->setStyle($indexName.$this->cellIndex,$this->fontStyle('Tohoma', TRUE));
                                        }
                                        $this->setAlignment(Alignment::VERTICAL_TOP,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                                    }
                                }

                                if($j == count($heading)-1) {
                                    $this->setStyle('B6:P6', $this->fontStyle());
                                    $this->setStyle('D6:P6', $this->allBorders());
                                    //Font styles cell C6 only
                                    $this->setStyle('C6',$this->fontStyle('Tohoma', TRUE));
                                }

                            } elseif($key == 2) {
                                //ANS heading
                                $indexName = chr($this->asciiB+count($getHeading[1])+1+$j);
                                $cellCoordinate = $indexName.$this->cellIndex;
                                if($j >= 3) {
                                    $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$heading[$j]);
                                    $this->setWidth($indexName, 22);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);

                                } else {
                                    $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$heading[$j]);
                                    $this->excel->getActiveSheet()->getColumnDimension($indexName)->setAutoSize(true);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                                }

                                if($j == count($heading)-1) {
                                    $this->setStyle('R6:V6', array_merge($this->fontStyle(), $this->background('dce6f1'), $this->allBorders()));
                                }

                            } else {
                                //IGW heading
                                if(ord(chr($this->asciiB+count($getHeading[1])+count($getHeading[2])+1+$j)) >= ord('Z')) {
                                    $indexName = chr($this->asciiA).chr($this->asciiA+$k);
                                    $cellCoordinate = $indexName.$this->cellIndex;

                                    $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$heading[$j]);
                                    $this->setWidth($indexName, 22);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);

                                    $k++;

                                } else {
                                    $indexName = chr($this->asciiB+count($getHeading[1])+count($getHeading[2])+2+$j);
                                    $cellCoordinate = $indexName.$this->cellIndex;
                                    $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$heading[$j]);
                                    $this->excel->getActiveSheet()->getColumnDimension($indexName)->setAutoSize(true);
                                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                                }

                                if($j == count($heading)-1) {
                                    $this->setStyle('X6:AB6', array_merge($this->fontStyle(), $this->background('dce6f1'), $this->allBorders()));
                                }
                            }
                        }
                    }
                }

            } else {

                $this->excel->createSheet(); //Worksheet Create
                $this->excel->setActiveSheetIndex($i); //Get Active worksheet
                $this->excel->getActiveSheet()->setTitle($getSheetName[$i]); //Set default worksheet name title

                //Zoom scale
                $this->zoomScale();

                //Set Height
                $this->rowHeight($this->cellIndex, 42); // Set row height

                //Basic info
                $this->basicInfoSetting(1);

                //Getting report main area heading
                $getHeading = $this->reportHeadingDetails();

                //Form-E Report heading
                for($j=0; $j < count($title = $getHeading[4]); $j++) {
                    //IOS heading
                    $indexName = chr($this->asciiB+$j);
                    $cellCoordinate = $indexName.$this->cellIndex;
                    if($j >= 5 && $j <=10) {
                        $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$title[$j]);
                        $this->setWidth($indexName, 22);
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                    } elseif($j >= 11 && $j < 15) {
                        $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$title[$j]);
                        $this->setWidth($indexName, 22);
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                    } elseif ($j >= 15 && $j < 16) {
                        $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$title[$j]);
                        $this->setWidth($indexName, 12);
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                    } elseif ($j >= 16) {
                        $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$title[$j]);
                        $this->setWidth($indexName, 22);
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                    }else {
                        $this->excel->getActiveSheet()->setCellValue($cellCoordinate,$title[$j])->getColumnDimension($indexName)->setAutoSize(true);
                        if($j > 1) {
                            $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                        } else {
                            $this->setAlignment(Alignment::VERTICAL_TOP,Alignment::HORIZONTAL_CENTER,$indexName.$this->cellIndex, TRUE);
                        }
                    }

                    if($j == count($getHeading[4])-1) {
                        $this->setStyle('B6:P6', $this->fontStyle());
                        $this->setStyle('D6:P6', $this->allBorders());
                        //Font styles cell C6 only
                        $this->setStyle('C6',$this->fontStyle('Tohoma', TRUE));
                    }
                }
            }

        }

        return $this->excel;
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

    /**
     * @return array
     */
    private function dataSorting(): array
    {

        $sql = $this->query();

        $tempCompanyId = array();
        $tempCompanyName = array();

        //sqlPart1 query processing
        foreach($sql[1] as $key => $val) {
            //JSON data to array conversion
            $s = get_object_vars($val);
            //$data = $s['precedence'].';'.$s['systemId'].';'.$s['shortName'];

            //Store only company ID
            array_push($tempCompanyId, $s['systemId']);

            //Store company name
            array_push($tempCompanyName, $val);
        }

        //Combine tempCompanyId and tempCompanyName
        //dump(array_combine($tempCompanyId, $tempCompanyName));
        $companyInfo = array_combine($tempCompanyId, $tempCompanyName);


        // #end sqlPart1 query processing

        //Source data sorting
        $cdrDetails = array();
        $companySysId = array();

        foreach ($sql[2] as $val) {
            //JSON data to array conversion
            $s = get_object_vars($val);
            array_push($cdrDetails, $val);
            array_push($companySysId, $s['companyID']);
        }

        //Combine systemId and cdr details
        $cdrInfo = array_combine($companySysId, $cdrDetails);
        //dump($tempCompanyId, $tempCompanyName, $cdrDetails, $companySysId);

        //$company = array_diff_key(array_combine($companyIdContainer, $companyNameContainer), array_combine($companySysId, $cdrDetails));

        //Finding matching 1
        $match1 = array_intersect_key($companyInfo, $cdrInfo);

        //Finding matching 2
        $match2 = array_intersect_key($cdrInfo,$companyInfo);

        //Finding unmatched data1
        $unMatched1 = array_diff_key($companyInfo, $cdrInfo);

        //Finding unmatched data2
        //$unMatched2 = array_diff_key($cdrInfo,$companyInfo);

        //dump($unMatched1, $unMatched2);

        //Works with matching data1
        $finalDataStore = array(); //Store matched and unmatched data

        //Store precedence
        $precedence = array();
        foreach($match1 as $key => $m1) {
            //JSON data to array conversion
            $sp1 = get_object_vars($m1);

            //JSON data to array conversion
            $sp2 = get_object_vars($match2[$key]);

            if($this->getDirection() == 1) {
                //Incoming data
                if($this->getType() == 4 || $this->getType() == 1) {
                    $data = $sp1['precedence'].';'.$sp1['shortName'].';'.$sp2['duration'];
                } else {
                    $data = $sp1['precedence'].';'.$sp1['shortName'].';'.$sp2['successfulCall'].';'.$sp2['duration'].';'. $sp2['ACD'];
                }
            } else {
                //Outgoing data
                if($this->getType() == 4 || $this->getType() == 1) {
                    //$data = $sp2['companyID'].' ===> '.$sp2['duration'].';'.$sp2['billDuration'];
                    $data = $sp2['duration'].';'.$sp2['billDuration'];
                } else {
                    //$data = $sp2['companyID'].' ===> '.$sp2['successfulCall'].';'.$sp2['duration'].';'.$sp2['billDuration'];
                    $data = $sp2['successfulCall'].';'.$sp2['duration'].';'.$sp2['billDuration'];
                }
            }

            //Store matched precedence
            array_push($precedence, $sp1['precedence']);

            //Store matched data
            array_push($finalDataStore, $data);
        }

        //Works with unmatched data1
        foreach ($unMatched1 as $key => $um1) {
            //JSON data to array conversion
            $um1 = get_object_vars($um1);
            //dump($um1);
            //Store unmatched precedence
            array_push($precedence, $um1['precedence']);

            if($this->getDirection() == 1) {
                //Incoming data
                if($this->getType() == 4 || $this->getType() == 1) {
                    $data = $um1['precedence'].';'.$um1['shortName'].';'.'0';
                } else {
                    $data = $um1['precedence'].';'.$um1['shortName'].';'.'0'.';'.'0'.';'.'0';
                }

            }else{
                //Outgoing data
                if($this->getType() == 4 || $this->getType() == 1) {
                    //$data = $um1['systemId'].'==>'.'0'.';'.'0';
                    $data = '0'.';'.'0';
                } else {
                    //$data = $um1['systemId'].'==>'.'0'.';'.'0'.';'.'0';
                    $data = '0'.';'.'0'.';'.'0';
                }
            }

            //Store unmatched data
            array_push($finalDataStore, $data);
        }

        //Data finally precedence wise sorting
        $orderlessData = array_combine($precedence, $finalDataStore);


        //Precedence key wise asc sorting
        ksort($orderlessData);



        //Pushing serial number with exiting data
        $data = array();

        $serial = 1;

        //Data finally sorting
        foreach ($orderlessData as $key=> $val) {
            //Empty string var for contain all sorting data
            $str = "";

            $rawData = explode(';', $val);

            if($this->getDirection() == 1) {

                //Incoming: Add serial number only incoming data
                for($i=1; $i < count($rawData); $i++) {
                    if($i < (count($rawData)-1)) {
                        $str .= $rawData[$i].';';
                    } else {
                        $str .= $rawData[$i];
                    }
                }

                array_push($data, $serial.';'.$str);

            } else {

                //Outgoing ignore serial number
                for($i=0; $i < count($rawData); $i++) {
                    if($i < (count($rawData)-1)) {
                        $str .= $rawData[$i].';';
                    } else {
                        $str .= $rawData[$i];
                    }
                }

                //Ignore serial number in OG data
                array_push($data, $str);
            }

            $serial++;
        }

        return $data;
    }

    /**
     * @return array
     */
    private function query(): array
    {
        //This query get data from local pc from company table
        $sqlPart1 = DB::connection('mysql')->table('iofcompanies')
            ->select('shortName','systemId','precedence')
            ->where('type','=', $this->getType())
            ->where('status','=', 1)
            ->get();

        //IGW query
        //This Query get data from server pc (CDR data main source)
        if($this->getDirection() == 1) {
            //Incoming query
            $sqlPart2 = DB::connection($this->getConnectionString())->table('CallSummary as cm')
                        ->join('Company as c', $this->getTableColumnOne(), '=', $this->getTableColumnTwo())
                        ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                            DB::raw("round((SUM(cm.CallDuration)/60),6,0) duration"),
                            DB::raw("round((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall),6,0) as ACD"))
                        ->whereBetween('cm.TrafficDate', array($this->getFromDate().' 00:00:00', $this->getToDate().' 23:59:59'))
                        ->where('cm.ReportTrafficDirection','=', $this->getDirection())
                        ->groupBy('c.CompanyID', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('c.CompanyID')
                        ->get();
        } else {

            //Outgoing query This query contain bill duration
            if($this->getTableColumnOne() != 'cm.ANSID') {
                //Only ignore if getting 'cm.ANSID' db schema
                $sqlPart2 = DB::connection($this->getConnectionString())->table('CallSummary as cm')
                            ->join('Company as c', $this->getTableColumnOne(), '=', $this->getTableColumnTwo())
                            ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                                DB::raw("round((SUM(cm.CallDuration)/60),6,0) duration"),
                                DB::raw("round(SUM(cm.BillDuration)/60, 6,0) as billDuration"),
                                DB::raw("round((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall),6,0) as ACD"))
                            ->whereBetween('cm.TrafficDate', array($this->getFromDate().' 00:00:00', $this->getToDate().' 23:59:59'))
                            ->where('cm.ReportTrafficDirection','=', $this->getDirection())
                            ->groupBy('c.CompanyID')
                            ->orderBy('c.CompanyID')
                            ->get();
            } else {
                //Only outgoing ANS Query
                $sqlPart2 = DB::connection($this->getConnectionString())->table('CallSummary as cm')
                            ->select('cm.ANSID as companyID',
                                DB::raw("SUM(cm.SuccessfulCall) as successfulCall"),
                                DB::raw("round((SUM(cm.CallDuration)/60),6,0) as duration"),
                                DB::raw("round(SUM(cm.BillDuration)/60, 6,0) as billDuration"),
                                DB::raw("round((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall),6,0) as ACD"))
                            ->whereBetween('cm.TrafficDate', array($this->getFromDate().' 00:00:00', $this->getToDate().' 23:59:59'))
                            ->where('cm.ReportTrafficDirection','=',$this->getDirection())
                            ->groupBy('cm.ANSID')
                            ->orderBy('cm.ANSID')
                            ->get();
            }
        }

        return ['1' => $sqlPart1, '2' => $sqlPart2];
    }

    //Incoming data container
    public $incomingData = array();

    /**
     * @return array
     */
    private function incomingData(): array
    {
        //dd($this->getplatform());
        $platform = array('3'=>'ICX', '4'=>'ANS','1'=>'IGW','2'=>'IOS');
        foreach ($platform as $key=>$pName) {
            //Set report type
            $this->setType($key);

            //Set report direction
            $this->setDirection(1);

            //Set db connection string
            if($key != 2) {
                //IOS connection
                $this->setConnectionString('sqlsrv2');
            } else {
                //IGW connection
                $this->setConnectionString('sqlsrv1');
            }

            //Send query wise value
            if($key == 1){
                $this->setTableColumnOne('cm.InCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }elseif($key == 2) {
                $this->setTableColumnOne('cm.OutCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }elseif($key == 3) {
                $this->setTableColumnOne('cm.OutCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }else {
                $this->setTableColumnOne('cm.ANSID');
                $this->setTableColumnTwo('c.CompanyID');
            }

            //Store incoming data
            array_push($this->incomingData, $this->dataSorting());
        }

        return $this->incomingData;
    }

    //Outgoing data container
    public $outgoingData = array();

    /**
     * @return array
     */
    private function outgoingData(): array
    {
        //dd($this->getplatform());
        $platform = array('3'=>'ICX', '4'=>'ANS','1'=>'IGW','2'=>'IOS');

        foreach ($platform as $key=>$pName) {

            //Set report type
            $this->setType($key);

            //Set report direction
            $this->setDirection(2);

            //Set db connection string
            if($key != 2) {
                //IOS connection
                $this->setConnectionString('sqlsrv2');
            } else {
                //IGW connection
                $this->setConnectionString('sqlsrv1');
            }

            //Send query wise value
            if($key == 1){
                $this->setTableColumnOne('cm.OutCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }elseif($key == 2) {
                $this->setTableColumnOne('cm.InCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }elseif($key == 3) {
                $this->setTableColumnOne('cm.InCompanyID');
                $this->setTableColumnTwo('c.CompanyID');
            }else {
                $this->setTableColumnOne('cm.ANSID');
                $this->setTableColumnTwo('c.CompanyID');
            }

            //Store outgoing data
            array_push($this->outgoingData, $this->dataSorting());
        }

        return $this->outgoingData;
    }

    private $dbRowCount = array();

    /**
     * @return Spreadsheet
     * @throws Exception
     */
    public function dataSetting(): Spreadsheet
    {
        //Store incoming and outgoing data
        $incoming = $this->incomingData();
        $outgoing = $this->outgoingData();

        foreach ($incoming as $key=> $cdr){
            //Combine incoming and outgoing data
            $incomingAndOutgoingData = array_combine($cdr, $outgoing[$key]);

            //dump($incomingAndOutgoingData);
            //Value set index name ICX and IOS
            $icxAndIosValueIndexName = array('B','C','E','G','H','K','M','N');

            //Format ignore index
            $icxAndIosFormatIndexName = array('B','C','H');

            //Contain total DB row based on query count
            array_push($this->dbRowCount,count($incomingAndOutgoingData));

            if($key != 3) {
               //C-Form data
                $this->excel->setActiveSheetIndex(0); //Default active worksheet.

                $j=7; //J value changing cell indexing

                //Raw data
                foreach ($incomingAndOutgoingData as $incomingRawData => $outgoingRawData){
                    //$connected = $incomingRawData.';'.$outgoingRawData;
                    $dataSplit = explode(';',$incomingRawData.';'.$outgoingRawData);
                    //dump($dataSplit);
                    if($key == 0){
                        $ascii = 66; //B ascii value
                    } elseif ($key == 1) {
                        $ascii = 82; //R ascii value
                    } else {
                        $ascii = 88; //X ascii value
                    }

                    //Data setting
                    for($i = 0; $i < count($dataSplit); $i++) {
                        if($key == 2) {
                            //Work with IGW data
                            //If worksheet index value is bigger then 'Z' this section is working
                            if((ord(chr($ascii+$i)) > ord('Z'))) {
                                //Create worksheet new index name like AA,AB,AC,AD ....
                                $indexName = chr($this->asciiA).chr($this->asciiA+$i-3); //3 first 3 column ignore so that -3 for this section
                                $this->excel->getActiveSheet()->setCellValue($indexName.$j,$dataSplit[$i]);
                                $this->numberFormat($indexName.$j, NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                            } else {
                                $this->excel->getActiveSheet()->setCellValue(chr($ascii+$i).$j,$dataSplit[$i]);
                                $this->numberFormat(chr($ascii+$i).$j, NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                            }

                        } else {
                            //Work with ICX and ANS data
                            if($key == 0) {
                                if(in_array($icxAndIosValueIndexName[$i], $icxAndIosFormatIndexName)) {
                                    $this->excel->getActiveSheet()->setCellValue($icxAndIosValueIndexName[$i].$j,$dataSplit[$i]);
                                    //Apply number format
                                    if($icxAndIosValueIndexName[$i] == 'H' && $dataSplit[$i] != '0') {
                                        $this->numberFormat($icxAndIosValueIndexName[$i].$j, NumberFormat::FORMAT_NUMBER_00);
                                    }
                                } else {
                                    $this->excel->getActiveSheet()->setCellValue($icxAndIosValueIndexName[$i].$j,$dataSplit[$i]);
                                    $this->numberFormat($icxAndIosValueIndexName[$i].$j, NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                                }
                            } else {
                                $this->excel->getActiveSheet()->setCellValue(chr($ascii+$i).$j,$dataSplit[$i]);
                                if($i > 1) {
                                    $this->numberFormat(chr($ascii+$i).$j, NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                                }
                            }
                        }
                    }

                    //Increase j value, according to the cell index
                    $j++;
                }

            } else {
                //E-Form data
                $this->excel->setActiveSheetIndex(1); //Default active worksheet.
                $j=7; //J value changing cell indexing
                foreach ($incomingAndOutgoingData as $incomingRawData => $outgoingRawData){
                    //$connected = $incomingRawData.';'.$outgoingRawData;
                    $dataSplit = explode(';',$incomingRawData.';'.$outgoingRawData);

                    for($i = 0; $i < count($dataSplit); $i++) {
                        if(in_array($icxAndIosValueIndexName[$i], $icxAndIosFormatIndexName)) {
                            $this->excel->getActiveSheet()->setCellValue($icxAndIosValueIndexName[$i].$j,$dataSplit[$i]);
                            //Apply number format
                            if($icxAndIosValueIndexName[$i] == 'H' && $dataSplit[$i] != '0') {
                                $this->numberFormat($icxAndIosValueIndexName[$i].$j, NumberFormat::FORMAT_NUMBER_00);
                            }
                        } else {
                            $this->excel->getActiveSheet()->setCellValue($icxAndIosValueIndexName[$i].$j,$dataSplit[$i]);
                            $this->numberFormat($icxAndIosValueIndexName[$i].$j, NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                        }
                    }
                    //Increase j value, according to the cell index
                    $j++;
                }
            }
        }

        //Apply worksheet styles and summation
        $this->styleSetting();

        return $this->excel;
    }

    //Worksheet styles and summation
    private function styleSetting() {
        //dump($this->dbRowCount);
        foreach ($this->dbRowCount as $key=>$rowNumber)
        {

                //C-Form
                $this->excel->setActiveSheetIndex(0); //Default active worksheet.

                if($key == 0 || $key == 3) {

                    if($key == 3) {
                        $this->excel->setActiveSheetIndex(1); //Default active worksheet.
                        $this->setStyle('Q6:R'.($rowNumber+6), array_merge($this->background('dce6f1'), $this->thinAndMediumOutlineBorder()));
                        $this->excel->getActiveSheet()->setCellValue('R7','Please attach graph in separate sheet')->mergeCells('R7:R'.($rowNumber+6));
                        $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,'R7', TRUE);

                        //Border
                        $this->setStyle('Q'.($rowNumber+(6+1)).':R'.($rowNumber+(6+1)), $this->thinAndMediumOutlineBorder());
                    }

                    $this->setStyle('B5:B6', $this->background('ffffff'));
                    $this->setStyle('B7:B'.($rowNumber+6), $this->allBorders());
                    $this->setStyle('B5:B'.($rowNumber+6), $this->outlineBorder());
                    $this->setStyle('C5:C6', $this->background('dce6f1'));
                    $this->setStyle('C7:C'.($rowNumber+6), $this->background('c5d9f1'));
                    $this->setStyle('C7:C'.($rowNumber+6), $this->allBorders());
                    $this->setStyle('C5:C'.($rowNumber+6), $this->outlineBorder());
                    $this->setStyle('D6:I'.($rowNumber+6), array_merge($this->background('dce6f1'), $this->thinAndMediumOutlineBorder()));
                    $this->setStyle('J6:P'.($rowNumber+6), array_merge($this->background('dce6f1'), $this->thinAndMediumOutlineBorder()));

                    //Summation
                    $this->excel->getActiveSheet()->setCellValue('B'.($rowNumber+(6+1)),'Total')->mergeCells('B'.($rowNumber+(6+1)).':C'.($rowNumber+(6+1)));
                    $this->setStyle('B'.($rowNumber+(6+1)).':C'.($rowNumber+(6+1)), $this->outlineBorder());
                    //Text Right Alignment
                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,'B'.($rowNumber+(6+1)), FALSE);

                    $averageCells = array('H','I','O','P');

                    for ($i = ord('D'); $i <= ord('P'); $i++){
                        $cellRange = '=IFERROR(sum('.chr($i).'7:'.chr($i).($rowNumber+6).'),0)';
                        if(!in_array(chr($i),$averageCells)) {
                            $this->excel->getActiveSheet()->setCellValue(chr($i).($rowNumber+(6+1)), $cellRange);
                            $this->numberFormat(chr($i).($rowNumber+(6+1)), NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                        } else {
                            $cell = chr($i).($rowNumber+(6+1));
                            $cellRange = '=IFERROR(AVERAGEIF('.chr($i).'7:'.chr($i).($rowNumber+6).',"<>0"),0)';
                            $this->excel->getActiveSheet()->setCellValue($cell, $cellRange);

                            //Getting calculated value
                            $getCalculatedValue = $this->excel->getActiveSheet()->getCell($cell)->getCalculatedValue();
                            if($getCalculatedValue > 0 ){
                                $this->numberFormat(chr($i).($rowNumber+(6+1)), NumberFormat::FORMAT_NUMBER_00);
                            } else {
                                $this->numberFormat(chr($i).($rowNumber+(6+1)), NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                            }
                        }
                    }

                    //borders
                    $this->setStyle('D'.($rowNumber+(6+1)).':I'.($rowNumber+(6+1)), $this->thinAndMediumOutlineBorder());
                    $this->setStyle('J'.($rowNumber+(6+1)).':P'.($rowNumber+(6+1)), $this->thinAndMediumOutlineBorder());

                } elseif ($key == 1) {
                    $this->setStyle('R6:R'.($rowNumber+6), array_merge($this->background('ffffff'), $this->thinAndMediumOutlineBorder()));
                    $this->setStyle('S6:S'.($rowNumber+6), $this->background('ffffff'));
                    $this->setStyle('T6:V'.($rowNumber+6), $this->background('dce6f1'));
                    $this->setStyle('S6:V'.($rowNumber+6), $this->thinAndMediumOutlineBorder());
                    //Summation
                    $this->excel->getActiveSheet()->setCellValue('R'.($rowNumber+(6+1)),'Total')->mergeCells('R'.($rowNumber+(6+1)).':S'.($rowNumber+(6+1)));
                    $this->setStyle('R'.($rowNumber+(6+1)).':S'.($rowNumber+(6+1)), $this->outlineBorder());
                    //Text Right Alignment
                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,'R'.($rowNumber+(6+1)), FALSE);

                    for ($i = ord('T'); $i <= ord('V'); $i++){
                        $cellRange = '=IFERROR(sum('.chr($i).'7:'.chr($i).($rowNumber+6).'),0)';
                        $this->excel->getActiveSheet()->setCellValue(chr($i).($rowNumber+(6+1)), $cellRange);
                        $this->numberFormat(chr($i).($rowNumber+(6+1)), NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                    }

                    $this->setStyle('T'.($rowNumber+(6+1)).':V'.($rowNumber+(6+1)), $this->thinAndMediumOutlineBorder());

                } else {

                    $this->setStyle('X6:X'.($rowNumber+6), array_merge($this->background('ffffff'), $this->thinAndMediumOutlineBorder()));
                    $this->setStyle('Y6:Y'.($rowNumber+6), $this->background('ffffff'));
                    $this->setStyle('Z6:AB'.($rowNumber+6), $this->background('dce6f1'));
                    $this->setStyle('Y6:AB'.($rowNumber+6), $this->thinAndMediumOutlineBorder());

                    //Summation
                    $this->excel->getActiveSheet()->setCellValue('X'.($rowNumber+(6+1)),'Total')->mergeCells('X'.($rowNumber+(6+1)).':Y'.($rowNumber+(6+1)));
                    $this->setStyle('X'.($rowNumber+(6+1)).':Y'.($rowNumber+(6+1)), $this->outlineBorder());
                    //Text Right Alignment
                    $this->setAlignment(Alignment::VERTICAL_CENTER,Alignment::HORIZONTAL_CENTER,'X'.($rowNumber+(6+1)), FALSE);

                    $igwSum = array('Z','AA','AB');
                    for ($i = 0; $i < count($igwSum); $i++){
                        $cellRange = '=IFERROR(sum('.$igwSum[$i].'7:'.$igwSum[$i].($rowNumber+6).'),0)';
                        $this->excel->getActiveSheet()->setCellValue($igwSum[$i].($rowNumber+(6+1)), $cellRange);
                        $this->numberFormat($igwSum[$i].($rowNumber+(6+1)), NumberFormat::FORMAT_NUMBER_SIMPLE_ACCOUNTING);
                    }

                    $this->setStyle('Z'.($rowNumber+(6+1)).':AB'.($rowNumber+(6+1)), $this->thinAndMediumOutlineBorder());
                }
        }

    }


    /**
     * @return Spreadsheet
     * @throws Exception
     */
    private function defaultSetting(): Spreadsheet
    {
        //Worksheet zoom scale
        $this->setZoomValue(70);

        //Worksheet background color
        $this->excel->getDefaultStyle()->applyFromArray(
            [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'f9f9f9',
                    ],
                ]
            ]
        );

        return $this->excel;
    }

    public function testing() {
        dd('End');
    }

    //Index

    /**
     * @param null $date
     * @param bool $scheduleGenerateType
     * @return RedirectResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateReport($date = null, bool $scheduleGenerateType = false): RedirectResponse
    {

//        request()->validate([
//            'fromDate'   => 'required',
//        ]);

        $reportDate = request()->reportDate ?? $date;

        $inputFromDate          = Carbon::parse($reportDate)->format('Ymd');
        $inputToDate            = Carbon::parse($reportDate)->format('Ymd');

        $processStartTime = microtime(TRUE);

        $this->authorInfo($this->excel); //Authors
        $this->defaultSetting();
        $this->setFromDate($inputFromDate); //From date
        $this->setToDate($inputToDate); //To date
        $this->createSheet();
        //$this->reportHeadingDetails();
        $this->dataSetting();

        //Default Active Worksheet 0
        $this->excel->setActiveSheetIndex(0);
        $filename = Carbon::parse($inputFromDate)->format('d.m.Y');
        $filename = 'Btrac_dr_'.$filename;
        $writer = new Xlsx($this->excel);
        $writer->setIncludeCharts(true);

        if($scheduleGenerateType) {
            $writer->save(public_path().'/platform/igwandios/iof/schedule/callsummary/'.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igwandios/iof/callsummary/'.$filename.'.xlsx');
        }
        //Disconnect Worksheets from memory
        $this->excel->disconnectWorksheets();
        unset($this->excel);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        return Redirect::to('platform/igwandios/report/iof/daily/call/summary/report')->with('success',"Report generated! Process execution time: $executionTime Seconds");
    }

    //Loading form with files (If file exist in the file stored directory)
    public function index() {
        $getFiles = Storage::disk('public')->files('platform/igwandios/iof/callsummary/');

        $files = array();
        foreach ($getFiles as $key => $file) {
            //dump($file);
            $split = explode('/', $file);
            array_push($files, $split[4]);
        }
        //return view('platform.icx.index', compact('reportFiles'));
        return view('platform.igwandios.iof.DailyReport.index', compact('files'));
    }

    //Download IOF Daily Comparison Report
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igwandios/iof/callsummary/'.$filename;
        return response()->download($file);
    }

    //Delete Generated Report
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igwandios/iof/callsummary/'.$filename);
        return Redirect::to('platform/igwandios/report/iof/daily/call/summary/report')->with('success','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'IOF '. Carbon::now()->subdays(1)->format('d-M-Y');
        $zip_file =  public_path(). '/platform/igwandios/iof/zipFiles/callsummary/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igwandios/iof/callsummary/';

        $getFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        $flag = 0;
        foreach ($getFiles as $name => $file) {
            // We're skipping all sub folders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $date.'/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
                $flag = 1;
            }
        }

        if($flag) {
            $zip->close();
            return response()->download($zip_file);
        }
        return Redirect::to('platform/igwandios/report/iof/daily/call/summary/report')->with('danger','Directory is empty. Please generate reports');
    }

    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igwandios/iof/callsummary/'));
        if($clean1) {
            return Redirect::to('platform/igwandios/report/iof/daily/call/summary/report')->with('success','All Reports Successfully Deleted');
        }
        return Redirect::to('platform/igwandios/report/iof/daily/call/summary/report')->with('danger','There a problem to delete files');
    }
}
