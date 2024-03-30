<?php

namespace App\Http\Controllers\IGW;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Query\CallSummaryIncomingQuery;
use App\Query\CallSummaryOutgoingQuery;
use App\Authors\AuthorInformation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class CallSummaryController extends Controller
{
    private $incomingExcel;
    private $outgoingExcel;
    private $screenshot;
    public $tableHeaderCoordinate = 7;
    public $tableDataCoordinate = 8;

    public function __construct()
    {
        $this->incomingExcel = new Spreadsheet();
        $this->outgoingExcel = new Spreadsheet();
        $this->screenshot    = new Spreadsheet();
    }

    //Set Border and text bold style
    public function fontBolder(): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => true,
                'size' => 10,
                'color' => [
                    'rgb' => '000000'
                ]
            ]
        ];
    }

    //Data contains table header style

    /**
     * @return array
     */
    public function header(): array
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
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
        ];
    }
    //Data contains table style

    /**
     * @return array
     */
    public function table(): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => false,
                'size' => 10
                // 'color' => [
                // 'rgb' => '000000'
                // ]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
                // ,
                // 'outline' => [
                //     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                // ]
            ]
        ];
    }

    //Data contains table style

    /**
     * @return array
     */
    public function table2(): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => false,
                'size' => 10,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
                ,
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
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
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
        ];
    }

    //Number Format

    /**
     * @return array
     */
    public function FormatNumber00(): array
    {
        return [
            'formatCode' => NumberFormat::FORMAT_NUMBER_00
        ];
    }

    /**
     * @return array
     */
    public function FormatNumberComma(): array
    {
        return [
            'formatCode' => NumberFormat::FORMAT_NUMBER_COMMA
        ];
    }

    //Percentage

    /**
     * @return array
     */
    public function FormatPercentage(): array
    {
        return [
            'formatCode' => NumberFormat::FORMAT_PERCENTAGE_00
        ];
    }

    //Text Color Design

    /**
     * @return string[][][]
     */
    public function textColor(): array
    {
        return [
            'font' => [
                'color' => ['rgb' => 'FF0000']
            ]
        ];
    }

    //Text Color Design

    /**
     * @param $colorCode
     * @return array[]
     */
    public function background($colorCode): array
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

    //Text Color Design

    /**
     * @param $colorCode
     * @return array
     */
    public function backgroundWithBorder($colorCode): array
    {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => $colorCode,
                ],
            ],
            'font' => [
                'name' => 'Times New Roman',
                'bold' => true,
                'size' => 10,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
        ];
    }

    /**
     * @param $colorCode
     * @return array
     */
    public function backgroundWithOutlineBorder($colorCode): array
    {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => $colorCode,
                ],
            ],
            'font' => [
                'name' => 'Times New Roman',
                'bold' => true,
                'size' => 10,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function stickyNoteDesign(): array
    {

        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'F3FF00',
                ],
            ],

            'font' => [
                'bold' => 'true',
                'size' => 10,
                'name' => 'Times New Roman',
                //'color' => ['argb' => 'ff0000'],
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
    public function summaryStickyDesign(): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => true,
                'size' => 10,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function summaryStickyHeadingDesign(): array
    {
        return [
            'font' => [
                'name' => 'Times New Roman',
                'bold' => true,
                'size' => 11,
                'underline' => Font::UNDERLINE_SINGLE,
                // 'strikethrough' => false,
            ]
        ];
    }

    //Set Author Info
    private function authorInfo($worksheet): void
    {
        $authorsInfo = AuthorInformation::authors();
        $worksheet->getProperties()
            ->setCreator($authorsInfo['creator'])
            ->setLastModifiedBy($authorsInfo['creator'])
            ->setTitle($authorsInfo['sTitle'])
            ->setSubject($authorsInfo['sSubject'])
            ->setDescription($authorsInfo['sDescription'])
            ->setKeywords($authorsInfo['sKeywords'])
            ->setCategory($authorsInfo['sCategory']);

    }

    //Work Sheet Name

    /**
     * @return string[]
     */
    private function IncomingWorkSheetName(): array
    {
        return [
            'OS',
            'OS IP Wise',
            'IOS',
            'ANS',
            'Daily',
            'Daily Duration Chart',
            //'TDM Call', //This sheet disabled 01-Jun-2021
            'IP Call'
        ];
    }

    //Work Sheet Name

    /**
     * @return string[]
     */
    private function OutgoingWorkSheetName(): array
    {
        return [
            'OS',
            'IOS',
            'Destination Wise',
            'Daily',
            'Daily Duration Chart'
        ];
    }

    //Sheet general information (like: heading, date, etc.)

    /**
     * @param $getFromDate
     * @param $getToDate
     * @return array
     */
    private function IncomingInformation($getFromDate, $getToDate): array
    {

        $getFromDate2 = Carbon::parse($getToDate)->subDays(35)->format('d-m-Y');

        return array(

            '0' => array(
                'Heading'       => 'Total International IC Traffic/Route wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Direction'     => 'Incoming',
                'Switch'        => 'All'
            ),

            '1' => array(
                'Heading'       => 'Total International IC Traffic/IP wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'OS',
                'Direction'     => 'Incoming',
                //'Switch'        => 'All'
            ),

            '2' => array(
                'Heading'       => 'Total International IC Traffic/ICX wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'IOS',
                'Direction'     => 'Outgoing'
            ),

            '3' => array(
                'Heading'       => 'Total International IC Traffic/ANS wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'ANS',
                'Direction'     => 'Outgoing'
            ),

            '4' => array(
                'Heading'       => 'Total International IC Traffic/Day wise Summary Report',
                'Period from'   => $getFromDate2.' 00:00:00 to '.$getToDate.' 23:59:59', //Range date
                'Client type'   => 'OS',
                'Direction'     => 'Incoming'
            ),

            // '5' => array(
            //     'Heading'       => 'Total International IC Traffic/Day wise Summary Report',
            //     'Period from'   => '09-08-2019 00:00:00 to 08-10-2019 23:59:59', //Range date
            //     'Client type'   => 'OS',
            //     'Direction'     => 'Incoming'
            // ),

            '5' => array(),
            //array value-> 6 This heading disabled 01-Jun-2021
//            '6' => array(
//                'Heading'       => 'Total International IC Traffic/Day wise TDM Summary Report',
//                'Period from'   => $getFromDate2.' 00:00:00 to '.$getToDate.' 23:59:59', //Range date
//                'Client type'   => 'OS',
//                'Direction'     => 'Incoming'
//            ),

            '6' => array(
                'Heading'       => 'Total International IC Traffic/Day wise IP Summary Report',
                'Period from'   => $getFromDate2.' 00:00:00 to '.$getToDate.' 23:59:59', //Range date
                'Client type'   => 'OS',
                'Direction'     => 'Incoming'
            ),
        );
    }

    //Sheet general information (like: heading, date, etc.)

    /**
     * @param $getFromDate
     * @param $getToDate
     * @return array
     */
    private function OutgoingInformation($getFromDate, $getToDate): array
    {

        $getFromDate2 = Carbon::parse($getToDate)->subDays(35)->format('d-m-Y');

        return array(

            '0' => array(
                'Heading'       => 'Total International OG Traffic/Route wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'OS',
                'Direction'     => 'Outgoing',
                'Switch'        => 'All'
            ),

            '1' => array(
                'Heading'       => 'Total International OG Traffic/Route wise summary report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'ICX/IOS',
                'Direction'     => 'Incoming',
                //'Switch'        => 'All'
            ),

            '2' => array(
                'Heading'       => 'Total International OG Traffic/Destination wise report',
                'Period from'   => $getFromDate.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'OS',
                'Direction'     => 'Outgoing',
                'Switch'        => 'All'
            ),

            '3' => array(
                'Heading'       => 'Total International OG Traffic/Daily Report',
                'Period from'   => $getFromDate2.' 00:00:00 to '.$getToDate.' 23:59:59',
                'Client type'   => 'OS',
                'Direction'     => 'Outgoing'
            ),
            '4' => array(),
        );
    }


    //Reports Table Heading

    /**
     * @return string[][]
     */
    public function IncomingHeadingName(): array
    {
        return [
            //OS Worksheet
            '0' => array('Sl No','OS Name','Successful Calls','Total Duration (min)','ACD (min)','Successful Calls %','Total Duration (min) %'),

            //OS IP Wise Worksheet
            '1' => array('Sl No','OS Name','IP','Successful Calls','Total Duration (min)','ACD (min)'),

            //IOS Worksheet
            '2' => array('Sl No','IOS Name','Traffic Date','Successful Calls','Total Duration (min)','ACD (min)','% of Successful Calls','% of Total Duration'),

            //ANS Worksheet
            '3' => array('Sl No','ANS Name','Successful Calls','Duration (min)','ACD (min)'),

            '4' => array('Sl No','Day & Date','Successful Calls','Duration (min)','ACD (min)'),
            //Chart
            '5' => array('Sl No','Day & Date','Successful Calls','Duration (min)','ACD (min)'), //Chart Sheet, but ignore this array
            //TDM Worksheet (This table heading disabled 01-Jun-2021)
            //'6' => array('Sl No','Day & Date','Successful Calls','Duration (min)','ACD (min)'), //This heading use in 3 worksheet
            //IP Worksheet  (This use in IP worksheet modified date: 07-Jun-2021)
            '6' => array('Sl No','Day & Date','Successful Calls','Duration (min)','ACD (min)') //This heading use in 3 worksheet
        ];
    }

    //Reports Table Heading

    /**
     * @return array
     */
    public function OutgoingHeadingName(): array
    {
        return [
            // //OS Worksheet
            '0' => array('Sl No','OS Name','Successful Calls','Total Duration (min)','ACD (min)','Successful Calls %','Total Duration (min) %'),

            //IOS Worksheet
            '1' => array('Sl No','IOS Name','Traffic Date','Successful Calls','Total Duration (min)','ACD (min)','% of Successful Calls','% of Total Duration'),

            //ANS Worksheet
            '2' => array('Sl No','OS Name','Country','Destination','Destination Code','No of Call ','Dur(Min)','Bill Dur(Min)'),

            '3' => array('Sl No','Day & Date','Successful Calls','Duration (min)','ACD (min)'),
            //Daily, TDM, IP Worksheet
            //'4' => array('Sl No','Date / Time','Successful Calls','Duration (min)','ACD (min)') //This heading use in 3 worksheet
            '4' => array()
        ];
    }

    //Create Worksheet Initial Setup
    private function WorksheetInitialSetup($reportType, $worksheet, $sheetName, $sheetGeneralInformation, $createAdditionalSheet = null) {

        //Create Worksheet and it's general information
        for($i = 0; $i < count($sheetName); $i++) {
            //0,1,2,3,4,5,6,7
            if( $i == 0 ) {
                $worksheet->setActiveSheetIndex(0); //First Worksheet
                $worksheet->getActiveSheet()->setTitle($sheetName[0]);
                $cellIndex = 1;

                //Sticky Note
                if($reportType == 1) {
                    $worksheet->getActiveSheet()
                        ->setCellValue('E2','Color Indicates Top 10 of reference column')
                        ->mergeCells('E2:G2')
                        ->getStyle('E2:G2')
                        ->applyFromArray($this->backgroundWithBorder('FFE5E0'));
                }

                foreach($sheetGeneralInformation[$i] as $key => $value) {
                    //echo 'A'.$cellIndex.' ', $key.':'.$value.'<br>';
                    $worksheet->getActiveSheet()
                        ->setCellValue('A'.$cellIndex, ($key == 'Heading') ? $value : $key.': '.$value)
                        ->mergeCells('A'.$cellIndex.':D'.$cellIndex)
                        ->getStyle(($key == 'Heading') ? 'A1:D1': 'A'.$cellIndex.':D'.$cellIndex)
                        ->applyFromArray(($key == 'Heading') ? $this->fontBolder(): ['font' => [
                            'name' => 'Times New Roman', 'size' => 10]]);
                    $cellIndex++;
                }
            } else {
                $worksheet->createSheet()->setTitle($sheetName[$i]); //Worksheet Create

                if($createAdditionalSheet != null && $sheetName[$i] == 'Daily Duration Chart') {
                    $fromDate = Carbon::now()->subDays($createAdditionalSheet+1)->format('Ymd');
                    $toDate = Carbon::now()->subDays(2)->format('Ymd');
                    $dates = $this->getDatesFromRange($fromDate,$toDate, 'd-M-Y');
                    arsort($dates);

                    for($k = $createAdditionalSheet-1; $k >= 0; $k-- ) {
                        $worksheet->createSheet()->setTitle($dates[$k]); //Worksheet Create
                    }
                    //dump($sheetName[$i]);
                    //Heading Setup
                    $collectDestinationHeader = $this->OutgoingHeadingName();

                    //dd($worksheet->getIndex($worksheet->getSheetByName($sheetName[$i]))); //Get worksheet index number by worksheet name
                    $getLastWorksheetIndex = $worksheet->getIndex($worksheet->getSheetByName($sheetName[$i]));

                    //Multiple days destination reports
                    $this->ifHasRequestForExtraDestinationReport($reportType, $worksheet, $collectDestinationHeader[2], $createAdditionalSheet, $dates, ($getLastWorksheetIndex+1));
                }

                if($i != 5) { // 5 is ignored for chart. so, skip this worksheet.
                    $worksheet->setActiveSheetIndex($i);
                    $cellIndex = 1;
                    foreach($sheetGeneralInformation[$i] as $key => $value) {
                        //echo $sheetName[$i].'---A'.$cellIndex.' ', $key.':'.$value.'<br>';
                        $worksheet->getActiveSheet()
                            ->setCellValue('A'.$cellIndex, ($key == 'Heading') ? $value : $key.': '.$value)
                            ->mergeCells('A'.$cellIndex.':D'.$cellIndex)
                            ->getStyle(($key == 'Heading') ? 'A1:D1': 'A'.$cellIndex.':D'.$cellIndex)
                            ->applyFromArray(($key == 'Heading') ? $this->fontBolder(): ['font' => [
                                'name' => 'Times New Roman', 'size' => 10]]);
                        $cellIndex++;
                    }
                }
            }
        }

        return $worksheet;
    }

    private function CreateIncomingWorksheet($fromDate,$toDate) {
        $inputFromDate    = Carbon::parse($fromDate)->format('d-m-Y');
        $inputToDate      = Carbon::parse($toDate)->format('d-m-Y');
        return $this->WorksheetInitialSetup(1, $this->incomingExcel, $this->IncomingWorkSheetName(), $this->IncomingInformation($inputFromDate, $inputToDate));
    }

    private function CreateOutgoingWorksheet($fromDate,$toDate, $additionalWorksheet = null) {
        $inputFromDate  = Carbon::parse($fromDate)->format('d-m-Y');
        $inputToDate    = Carbon::parse($toDate)->format('d-m-Y');
        return $this->WorksheetInitialSetup(2, $this->outgoingExcel, $this->OutgoingWorkSheetName(), $this->OutgoingInformation($inputFromDate, $inputToDate), $additionalWorksheet);
    }

    private function IncomingHeading() {
        return $this->HeadingSetup(1, $this->incomingExcel,$this->IncomingHeadingName()); // 1 is report type (Incoming report)
    }

    private function OutgoingHeading() {
        return $this->HeadingSetup(2, $this->outgoingExcel, $this->OutgoingHeadingName()); // 2 is report type (Outgoing report)
    }

    //Data contains table header create
    private function HeadingSetup($reportType, $worksheet, $HeadingName) {

        $skipWorksheet = null;
        if($reportType == 1) {
            $skipWorksheet = 5;
        } else {
            $skipWorksheet = 4;
        }

        //Set right alignment header name;
        $rightAlignHeader = array('Successful Calls','Total Duration (min)', 'Duration (min)', 'ACD (min)', 'Successful Calls %', 'Total Duration (min) %', '% of Successful Calls', '% of Total Duration');

        for($i = 0; $i < count($HeadingName); $i++) { //Heading count value and total worksheets number both are equal
            $asciiValue = 65;  // 65 is 'A' ascii value B-66, C-67, D-68;
            if($i != $skipWorksheet) { // 5 is ignored for chart. so, skip this worksheet.

                $worksheet->setActiveSheetIndex($i);

                //echo 'Outer'.count($HeadingName[$i]).'<br>';
                for($j = 0; $j < count($HeadingName[$i]); $j++) {
                    // echo $j.'---'.count($HeadingName[$i]);
                    $cellIndex = $this->tableHeaderCoordinate; //Heading starting Excel cell index 7 (A7)
                    $indexName = chr($asciiValue);
                    $asciiLastValue = $asciiValue;
                    //var_dump($v);
                    $name = $HeadingName[$i][$j];
                    //echo $name;
                    if(in_array($name, $rightAlignHeader)) {
                        $worksheet->getActiveSheet()->setCellValue($indexName.$cellIndex, $name)->getStyle($indexName.$cellIndex)->getAlignment()->applyFromArray(['horizontal'   => Alignment::HORIZONTAL_RIGHT]);
                    } else {

                        $worksheet->getActiveSheet()->setCellValue($indexName.$cellIndex, $name);
                    }

                    //echo $k.'---'.$indexCount.'+++'.$j;
                    if($j == (count($HeadingName[$i]) - 1)) {
                        $coordinateRange = 'A'.$this->tableHeaderCoordinate.':'.chr($asciiLastValue).$cellIndex;
                        $worksheet->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->header());
                        if($i == 0 && $reportType == 1) {
                            $worksheet->getActiveSheet()->getStyle($coordinateRange)->applyFromArray(
                                [
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => [
                                            'argb' => 'FFF300',
                                        ]
                                    ]
                                ]
                            );
                        }
                    }

                    //Auto Resize Column
                    $worksheet->getActiveSheet()->getColumnDimension($indexName)->setAutoSize(true);

                    $cellIndex++;
                    $asciiValue++;
                }

                //Worksheet 0 and 2 header height setting for incoming report
                if($reportType == 1) {
                    if($i == 0 || $i == 2) {
                        //echo $i;
                        if($i == 0) {
                            $get_D_index = 68;
                            for($a = $get_D_index; $a <= 71; $a++) { //68 is B ascii value;
                                //$a = 68; $a= 69; $a = 70; $a = 71; $a = 72(false)
                                $worksheet->getActiveSheet()->getColumnDimension(chr($a))->setAutoSize(false);
                                $worksheet->getActiveSheet()->getColumnDimension(chr($a))->setWidth(15);
                                if($a == 71) {
                                    //echo chr($get_D_index).'6:'.chr($a).'6'.'<br>';
                                    $worksheet->getActiveSheet()->getStyle(chr($get_D_index).$this->tableHeaderCoordinate.':'.chr($a).$this->tableHeaderCoordinate)->getAlignment()->applyFromArray([
                                        'horizontal'   => Alignment::HORIZONTAL_CENTER,
                                        'wrapText'     => TRUE
                                    ]);
                                }

                            }
                            $worksheet->getActiveSheet()->getRowDimension($this->tableHeaderCoordinate)->setRowHeight(40); // 7 is cell index value;
                        } else {
                            $worksheet->getActiveSheet()->getRowDimension($this->tableHeaderCoordinate)->setRowHeight(30); // 7 is cell index value;
                        }

                    }
                } else {
                    if($i == 1) {
                        $worksheet->getActiveSheet()->getRowDimension($this->tableHeaderCoordinate)->setRowHeight(30); // 7 is cell index value;
                    }
                }

            }
        }
        //echo "Header Crated Successfully<br>";
    }

    private function ifHasRequestForExtraDestinationReport($reportType, $worksheet, $HeadingName, $createAdditionalSheet, $dates, $getIndex) {
        $createAdditionalSheet = $createAdditionalSheet-1; //1 decrement for perfect result

        $arrayInfo = array();
        $queries = array();
        for($k = $createAdditionalSheet; $k >= 0; $k--) {
            array_push($arrayInfo,
                array('1'=> 'Total International OG Traffic/Destination wise report',
                    '2'=> 'Period from: '.$dates[$k].' 00:00:00 to '.$dates[$k].' 23:59:59',
                    '3'=> 'Client type: OS',
                    '4'=> 'Direction: Outgoing',
                    '5'=> 'Switch: All'
                ));
            array_push($queries, CallSummaryOutgoingQuery::DesWiseOutgoing($dates[$k].' 00:00:00', $dates[$k].' 23:59:59'));
        }

        if($reportType == 2) {

            $cellIndex = 0;

            for($i = $getIndex; $i <= ($getIndex+$createAdditionalSheet); $i++) {
                $worksheet->setActiveSheetIndex($i);

                //dd($arrayInfo[$i-$i]);
                $getArrayIndex = ($i-$i)+$cellIndex;
                //var_dump($getArrayIndex);
                $headingStartingIndex = $this->tableHeaderCoordinate; //Heading starting Excel cell index 7 (A7)

                //Cell wise info setup
                foreach($arrayInfo[$getArrayIndex] as $key => $value) {
                    $worksheet->getActiveSheet()->setCellValue('A'.$key, $value)
                        ->mergeCells('A'.$key.':D'.$key)
                        ->getStyle(($key == 1) ? 'A1:D1': 'A'.$key.':D'.$key)
                        ->applyFromArray(($key == 1) ? $this->fontBolder(): ['font' => [
                            'name' => 'Times New Roman', 'size' => 10]]);
                }

                //dd($HeadingName);
                $asciiValue = 65;  // 65 is 'A' ascii value B-66, C-67, D-68;
                for($j = 0; $j < count($HeadingName); $j++) {
                    $indexName = chr($asciiValue);
                    $asciiLastValue = $asciiValue;
                    $name = $HeadingName[$j];

                    $worksheet->getActiveSheet()->setCellValue($indexName.$headingStartingIndex, $name)->getColumnDimension($indexName)->setAutoSize(true);

                    if($j == (count($HeadingName) - 1)) {
                        $coordinateRange = 'A'.$this->tableHeaderCoordinate.':'.chr($asciiLastValue).$this->tableHeaderCoordinate;
                        $worksheet->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->header());
                    }
                    $asciiValue++;
                }

                //Cell wise data set
                $asciiValue = 66;  // 66 is 'B' ascii value
                $schema = $this->OutgoingSchema();
                $schemaName = $schema[2]; //Get destination schema from OG schema array
                $schemaArray = array('SuccessfulCall', 'Duration', 'BillDuration');

                foreach($queries[$getArrayIndex] as $key => $totalData) {
                    $objData = get_object_vars($totalData);
                    $endIndexValue = 0;
                    $totalQueryValueCount = 0;
                    $keyCount = $key;

                    $startCellCoordinate = $this->tableDataCoordinate+$key; // 8+$key value, $key value is 1 increment each iteration
                    $worksheet->getActiveSheet()->setCellValue(chr($asciiValue-1).$startCellCoordinate, $key+1); //Serial print, char($asciiValue-1) = 65 'A' value
                    for($j = 0; $j < count($schemaName); $j++) {
                        $endIndexValue = $asciiValue+$j; //collect end index
                        if(in_array($schemaName[$j], $schemaArray)) {
                            if($schemaName[$j] == 'SuccessfulCall' || $schemaName[$j] == 'Duration' || $schemaName[$j] == 'BillDuration') {
                                $worksheet->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schemaName[$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            }
                        } elseif($schemaName[$j] == 'DestinationCode') {
                            //Set Destination Code
                            $worksheet->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schemaName[$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getAlignment()->applyFromArray(['horizontal'   => Alignment::HORIZONTAL_LEFT]);
                        } else {
                            //Set Client Name in all Worksheet
                            $worksheet->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schemaName[$j]]);
                        }
                    }

                    //Summation Section
                    if($keyCount == (count($queries[$getArrayIndex]) - 1)) {
                        $coordinateRange        = 'A'.$this->tableDataCoordinate.':'.chr($endIndexValue).(count($queries[$getArrayIndex])+$this->tableHeaderCoordinate);
                        $totalQueryValueCount   = (count($queries[$getArrayIndex])+8);
                        $LastCoordinateRange    = 'A'.$totalQueryValueCount.':'.chr($endIndexValue).$totalQueryValueCount;

                        $worksheet->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->table2());
                        $worksheet->getActiveSheet()->setCellValue('A'.($totalQueryValueCount), 'Total:')->getStyle($LastCoordinateRange)->applyFromArray($this->footer());
                        $worksheet->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=SUM(F'.$this->tableDataCoordinate.':F'.(count($queries[$getArrayIndex])+$this->tableHeaderCoordinate).')')->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                        $worksheet->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=SUM(G'.$this->tableDataCoordinate.':G'.(count($queries[$getArrayIndex])+$this->tableHeaderCoordinate).')')->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                        $worksheet->getActiveSheet()->setCellValue('H'.$totalQueryValueCount, '=SUM(H'.$this->tableDataCoordinate.':H'.(count($queries[$getArrayIndex])+$this->tableHeaderCoordinate).')')->getStyle('H'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                    }
                }

                $cellIndex++;
            }
        }
    }

    /**
     * @param $getFromDate
     * @param $getToDate
     * @return array
     */
    public function CallSummaryIncomingQuery($getFromDate, $getToDate): array
    {

        $fromDate   = $getFromDate.' 00:00:00';
        $toDate     = $getToDate.' 23:59:59';
        $toDate2    = $getToDate;

        $OSWiseIn   = CallSummaryIncomingQuery::OSWiseIncoming($fromDate, $toDate);
        $OSIPWiseIn = CallSummaryIncomingQuery::OSIPWiseIncoming($fromDate, $toDate);
        $IOSWiseIn  = CallSummaryIncomingQuery::IOSWiseIncoming($fromDate, $toDate);
        $ANSWiseIn  = CallSummaryIncomingQuery::ANSWiseIncoming($fromDate, $toDate);
        $DailyIn    = CallSummaryIncomingQuery::DailyIncoming($toDate2);
        //$TDMIn      = CallSummaryIncomingQuery::TDMWiseIncoming($toDate2);
        $IPIn       = CallSummaryIncomingQuery::IPWiseIncoming($toDate2);

        //return ['0' => $OSWiseIn, '1' => $OSIPWiseIn, '2' => $IOSWiseIn, '3' => $ANSWiseIn, '4' => $DailyIn, '6' => $TDMIn,'7' => $IPIn];
        return ['0' => $OSWiseIn, '1' => $OSIPWiseIn, '2' => $IOSWiseIn, '3' => $ANSWiseIn, '4' => $DailyIn, '6' => $IPIn];
    }

    /**
     * @return array
     */
    private function IncomingSchema(): array
    {
        return [
            ['ShortName','SuccessfulCall', 'Duration', 'ACD', 'successfulCallsPercent', 'totalDurationPercent'],
            ['ShortName','IPAddress', 'SuccessfulCall', 'Duration', 'ACD'],
            ['ShortName','TrafficDate','SuccessfulCall', 'Duration', 'ACD', 'successfulCallsPercent', 'totalDurationPercent'],
            ['ShortName','SuccessfulCall', 'Duration', 'ACD'],
            ['traffic_date','SuccessfulCall', 'Duration', 'ACD'],
            [], //this empty array is ignored, because there will be a chart
//                ['traffic_date','SuccessfulCall', 'Duration', 'ACD'], //This db query schema disabled: 01-Jun-2021
            ['traffic_date','SuccessfulCall', 'Duration', 'ACD']
        ];
    }

    /**
     * @param array $queries
     * @param $schema
     * @param $schemaIndex
     * @return array
     */
    private function maxCount($queries = array(), $schema, $schemaIndex): array
    {
        $emptyArray = array();
        $topTen = array();

        foreach($queries as $key => $data) {
            $objData = get_object_vars($data);
            //dd($objData[$schema[$i][1]]);
            array_push($emptyArray,$objData[$schema[$schemaIndex]]);
        }

        rsort($emptyArray);

        foreach($emptyArray as $key => $value) {
            if($key < 10) {
                array_push($topTen, $value);
            }
        }
        return $topTen;
    }

    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @return bool
     * @throws Exception
     */
    private function IncomingDataSetter($inputFromDate, $inputToDate): bool
    {
        $schema  = $this->IncomingSchema();
        $queries = $this->CallSummaryIncomingQuery($inputFromDate, $inputToDate);

        $ansQueryResultCount = count($queries[3]); //Total ANS result count
        $dailyQueryResultCount = count($queries[4]); //Total Days count
        //dd(count($queries[3]));
        for($i = 0; $i <= 6; $i++) { //$i contains number of worksheet
            if($i != 5) {
                $this->incomingExcel->setActiveSheetIndex($i); //Get Active Worksheet
                //Max Count (Max Sorting) (Finding Max Value)
                if($i == 0) { //this condition true only for worksheet 0
                    //$schema = $schema[$i];
                    $maxSuccessfulCall  = $this->maxCount($queries[$i], $schema[$i], 1); //1 is 'SuccessfulCall' and 2 is 'Duration'
                    $maxDuration        = $this->maxCount($queries[$i], $schema[$i], 2); //1 is 'SuccessfulCall' and 2 is 'Duration'
                    //dd($maxDuration);
                }

                $keyCount = 0;

                foreach($queries[$i] as $key => $data) {
                    $objData = get_object_vars($data);
                    $endIndexValue = 0;
                    $keyCount = $key;
                    $asciiValue = 66;  // 66 is 'B' ascii value
                    $startCellCoordinate = $this->tableDataCoordinate+$key; // 8+$key value, $key value is 1 increment each iteration
                    $schemaArray = array('SuccessfulCall', 'Duration', 'ACD', 'successfulCallsPercent', 'totalDurationPercent');
                    $dayArray = array('Friday');
                    $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue-1).$startCellCoordinate, $key+1); //Serial print, char($asciiValue-1) = 65 'A' value

                    for($j = 0; $j < count($schema[$i]); $j++) {
                        //echo chr($asciiValue+$j).$startCellCoordinate.' == '.$objData[$schema[$i][$j]];
                        //echo $schema[$i][$j].'<br>';
                        $endIndexValue = $asciiValue+$j;
                        if(in_array($schema[$i][$j], $schemaArray)) {
                            if($schema[$i][$j] == 'SuccessfulCall' || $schema[$i][$j] == 'Duration') {
                                if($i == 0) { //this condition true only for worksheet 0
                                    if($schema[$i][$j] == 'SuccessfulCall') {
                                        //Set Successful Value
                                        if(in_array($objData[$schema[$i][$j]], $maxSuccessfulCall)) {
                                            //Set Max Successful Call
                                            $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                            $setBackground = array(chr(($asciiValue-1)+$j).$startCellCoordinate.':'.chr($asciiValue+$j).$startCellCoordinate, chr(($asciiValue+3)+$j).$startCellCoordinate);
                                            foreach($setBackground as $coordinate) {
                                                $this->incomingExcel->getActiveSheet()->getStyle($coordinate)->applyFromArray($this->background('A5D7FF'));
                                            }
                                        } else {
                                            //Set Successful Call in worksheet 0;
                                            $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                        }
                                    } else {
                                        if(in_array($objData[$schema[$i][$j]], $maxDuration)) {
                                            //echo "<span style='color:red; font-size:20px;'>".$objData[$schema[$i][$j]].'</span><br>';
                                            //Set Max Duration
                                            $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                            $setBackground = array(chr($asciiValue+$j).$startCellCoordinate.':'.chr($asciiValue+$j).$startCellCoordinate, chr(($asciiValue+3)+$j).$startCellCoordinate);
                                            foreach($setBackground as $coordinate) {
                                                $this->incomingExcel->getActiveSheet()->getStyle($coordinate)->applyFromArray($this->background('FFCFBB'));
                                            }
                                        } else {
                                            //Set Duration in worksheet 0;
                                            $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                        }
                                    }
                                } else {
                                    //Set Successful and Duration Value in all worksheet. expect worksheet 0 (OS sheet)
                                    $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                }
                            } elseif($schema[$i][$j] == 'ACD'){
                                //Set ACD value in all worksheet
                                $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                            } else {
                                //Set Percent value if the worksheet needed
                                $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                            }
                        } else {

                            if($schema[$i][$j] == 'traffic_date') {
                                //Set Traffic Date
                                $getDay = Carbon::parse($objData[$schema[$i][$j]])->format('l'); //Get Day name
                                //\Carbon\Carbon::parse($objData[$schema[$i][$j]])->format('d M y');
                                $coordinateRange = chr(($asciiValue-1)+$j).$startCellCoordinate.':'.chr($asciiValue+$j).$endIndexValue;

                                if(in_array($getDay, $dayArray)) {
                                    //Find Friday in an array.
                                    $getFirstIndexName = $endIndexValue-1;
                                    $colorCoordinateRange = chr($getFirstIndexName).$startCellCoordinate.':'.chr(($endIndexValue-1)+count($schema[$i])).$startCellCoordinate;
                                    $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, Carbon::parse($objData[$schema[$i][$j]])->format('D, d M Y'))->getStyle($colorCoordinateRange)->applyFromArray($this->textColor()); // set friday with red mark
                                } else {
                                    $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, Carbon::parse($objData[$schema[$i][$j]])->format('D, d M Y')); // set normal date
                                }

                            } else {

                                if($schema[$i][$j] == 'IPAddress') {
                                    if($objData[$schema[$i][$j]] == "") {
                                        $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, '0.0.0.0'); //fill with 0.0.0.0 instead of the ip fields is empty.
                                    } else {
                                        $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]]); // Ip value set
                                    }
                                } else {
                                    //Set Client Name in all Worksheet
                                    //echo $objData[$schema[$i][$j]].'<br>';
                                    $this->incomingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]]);
                                }
                            }
                        }
                    }

                    if($keyCount == (count($queries[$i]) - 1)) {
                        $coordinateRange        = 'A'.$this->tableDataCoordinate.':'.chr($endIndexValue).(count($queries[$i])+$this->tableHeaderCoordinate);
                        $totalQueryValueCount   = (count($queries[$i])+8);
                        $LastCoordinateRange    = 'A'.$totalQueryValueCount.':'.chr($endIndexValue).$totalQueryValueCount;
                        $isSummationWorksheet   = array(0,1,2,3);

                        //Worksheet-1
                        if(in_array($i, $isSummationWorksheet)) {
                            //echo $i;
                            //echo count($schema[$i]);
                            $this->incomingExcel->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->table2());
                            $this->incomingExcel->getActiveSheet()
                                ->setCellValue('A'.($totalQueryValueCount), 'Total:')
                                ->getStyle($LastCoordinateRange)
                                ->applyFromArray($this->footer());
                            if($i == 0) {
                                $this->incomingExcel->getActiveSheet()->setCellValue('C'.$totalQueryValueCount, '=SUM(C'.$this->tableDataCoordinate.':C'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('C'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/C'.$totalQueryValueCount)->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                                $this->incomingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=C'.$totalQueryValueCount.'/C'.$totalQueryValueCount)->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                                $this->incomingExcel->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                            } elseif($i == 1) {
                                $this->incomingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=SUM(E'.$this->tableDataCoordinate.':E'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=E'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                            } elseif($i == 2) {
                                $this->incomingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=SUM(E'.$this->tableDataCoordinate.':E'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=E'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                                $this->incomingExcel->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                                $this->incomingExcel->getActiveSheet()->setCellValue('H'.$totalQueryValueCount, '=E'.$totalQueryValueCount.'/E'.$totalQueryValueCount)->getStyle('H'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                            } else {
                                $this->incomingExcel->getActiveSheet()->setCellValue('C'.$totalQueryValueCount, '=SUM(C'.$this->tableDataCoordinate.':C'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('C'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                $this->incomingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/C'.$totalQueryValueCount)->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                            }
                        } else {
                            $this->incomingExcel->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->table());
                        }

                    }

                    //IP And TDM short summary in 'OS IP Wise' worksheet (This sticky note disabled: 01-Jun-2021)
//                    if($i == 1) {
//                        $this->stickyNote($this->incomingExcel, count($queries[$i]));
//                    }
                }
            }
        }

        //Add Chart
        $this->incomingANSChartOne($ansQueryResultCount);
        $this->incomingANSChartTwo($ansQueryResultCount);
        $this->dailyChartRender($this->incomingExcel, 5, $dailyQueryResultCount, 'IGW IC Minute');

        return true;
    }

    //StickyNote

    /**
     * @param $worksheet
     * @param $indexNumber
     * @return bool
     */
    private function stickyNote($worksheet, $indexNumber): bool
    {
        $startIndex = ($indexNumber+11);
        $endIndex = ($startIndex+1);

        //Wrap table with design
        $worksheet->getActiveSheet()->getStyle('C'.$startIndex.':F'.$endIndex)->applyFromArray($this->stickyNoteDesign());

        $worksheet->getActiveSheet()->setCellValue('C'.$startIndex,'TDM');
        $worksheet->getActiveSheet()->setCellValue('C'.($startIndex+1),'IP');

        //=SUMIF($C$8:$C$74,"=0.0.0.0",D8:D$74) //TDM

        //TDM Calls
        $worksheet->getActiveSheet()->setCellValue('D'.$startIndex, '=SUMIF($C$8:$C'.($indexNumber+$this->tableHeaderCoordinate).',"=0.0.0.0", D8:D$'.($indexNumber+$this->tableHeaderCoordinate).')')->getStyle('D'.$startIndex)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
        //TDM Minutes
        $worksheet->getActiveSheet()->setCellValue('E'.$startIndex, '=SUMIF($C$8:$C'.($indexNumber+$this->tableHeaderCoordinate).',"=0.0.0.0", E8:E$'.($indexNumber+$this->tableHeaderCoordinate).')')->getStyle('E'.$startIndex)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
        //TDM ACD
        $worksheet->getActiveSheet()->setCellValue('F'.$startIndex, '=E'.$startIndex.'/D'.$startIndex)->getStyle('F'.$startIndex)->getNumberFormat()->applyFromArray($this->FormatNumber00());

        //=SUMIF($C$8:$C$74,"<>0.0.0.0",D8:D$74) //IP
        //IP Calls
        $worksheet->getActiveSheet()->setCellValue('D'.$endIndex, '=SUMIF($C$8:$C'.($indexNumber+$this->tableHeaderCoordinate).',"<>0.0.0.0", D8:D$'.($indexNumber+$this->tableHeaderCoordinate).')')->getStyle('D'.$endIndex)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
        //IP Minutes
        $worksheet->getActiveSheet()->setCellValue('E'.$endIndex, '=SUMIF($C$8:$C'.($indexNumber+$this->tableHeaderCoordinate).',"<>0.0.0.0", E8:E$'.($indexNumber+$this->tableHeaderCoordinate).')')->getStyle('E'.$endIndex)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
        //IP ACD
        $worksheet->getActiveSheet()->setCellValue('F'.$endIndex, '=E'.$endIndex.'/D'.$endIndex)->getStyle('F'.$endIndex)->getNumberFormat()->applyFromArray($this->FormatNumber00());

        return true;
    }

    /**
     * @param $getFromDate
     * @param $getToDate
     * @return array
     */
    public function CallSummaryOutgoingQuery($getFromDate, $getToDate): array
    {

        $fromDate   = $getFromDate.' 00:00:00';
        $toDate     = $getToDate.' 23:59:59';
        $toDate2    = $getToDate;

        $OSWiseOut      = CallSummaryOutgoingQuery::OSWiseOutgoing($fromDate, $toDate);
        $IOSWiseOut     = CallSummaryOutgoingQuery::IOSWiseOutgoing($fromDate, $toDate);
        $DesWiseOut     = CallSummaryOutgoingQuery::DesWiseOutgoing($fromDate, $toDate);
        $DailyOut       = CallSummaryOutgoingQuery::DailyOutgoing($toDate2);

        return ['0' => $OSWiseOut, '1' => $IOSWiseOut, '2' => $DesWiseOut, '3' => $DailyOut];
    }

    /**
     * @return string[][]
     */
    private function OutgoingSchema(): array
    {
        return [
            ['ShortName','SuccessfulCall', 'Duration', 'ACD', 'successfulCallsPercent', 'totalDurationPercent'],
            ['ShortName','TrafficDate','SuccessfulCall', 'Duration', 'ACD', 'successfulCallsPercent', 'totalDurationPercent'],
            ['ShortName','Country', 'Destination', 'DestinationCode', 'SuccessfulCall', 'Duration','BillDuration'],
            ['traffic_date','SuccessfulCall', 'Duration', 'ACD']
        ];
    }

    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @return bool
     * @throws Exception
     */
    private function OutgoingDataSetter($inputFromDate, $inputToDate): bool
    {
        $schema  = $this->OutgoingSchema();
        $queries = $this->CallSummaryOutgoingQuery($inputFromDate, $inputToDate);
        $iosQueryResultCount = count($queries[1]); //Total ANS result count
        $dailyQueryResultCount = count($queries[3]); //Total Days count
        //echo "<pre>";
        for($i = 0; $i <= 3; $i++) { //$i contains number of worksheet
            $this->outgoingExcel->setActiveSheetIndex($i); //Get Active Worksheet
            foreach($queries[$i] as $key => $data) {
                $objData = get_object_vars($data);
                $endIndexValue = 0;
                $keyCount = $key;
                $asciiValue = 66;  // 66 is 'B' ascii value
                $startCellCoordinate = $this->tableDataCoordinate+$key; // 8+$key value, $key value is 1 increment each iteration
                $schemaArray = array('SuccessfulCall', 'Duration', 'ACD', 'BillDuration', 'successfulCallsPercent', 'totalDurationPercent');
                $dayArray = array('Friday');
                $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue-1).$startCellCoordinate, $key+1); //Serial print, char($asciiValue-1) = 65 'A' value

                for($j = 0; $j < count($schema[$i]); $j++) {
                    $endIndexValue = $asciiValue+$j;
                    if(in_array($schema[$i][$j], $schemaArray)) {
                        if($schema[$i][$j] == 'SuccessfulCall' || $schema[$i][$j] == 'Duration' || $schema[$i][$j] == 'BillDuration') {
                            if($i == 0) { //this condition true only for worksheet 0
                                if($schema[$i][$j] == 'SuccessfulCall') {
                                    //Set Successful Call in worksheet 0;
                                    $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                } else {
                                    //Set Duration in worksheet 0;
                                    $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                                }
                            } else {
                                //Set Successful and Duration Value in all worksheet. expect worksheet 0 (OS sheet)
                                $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            }
                        } elseif($schema[$i][$j] == 'ACD'){
                            //Set ACD value in all worksheet
                            $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                        } else {
                            //Set Percent value if the worksheet needed
                            $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                        }
                    } else {
                        if($schema[$i][$j] == 'traffic_date') {
                            //Set Traffic Date
                            $getDay = Carbon::parse($objData[$schema[$i][$j]])->format('l'); //Get Day name
                            //\Carbon\Carbon::parse($objData[$schema[$i][$j]])->format('d M y');
                            $coordinateRange = chr(($asciiValue-1)+$j).$startCellCoordinate.':'.chr($asciiValue+$j).$endIndexValue;

                            if(in_array($getDay, $dayArray)) {
                                //Find Friday in an array.
                                $getFirstIndexName = $endIndexValue-1;
                                $colorCoordinateRange = chr($getFirstIndexName).$startCellCoordinate.':'.chr(($endIndexValue-1)+count($schema[$i])).$startCellCoordinate;
                                $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, Carbon::parse($objData[$schema[$i][$j]])->format('D, d M Y'))->getStyle($colorCoordinateRange)->applyFromArray($this->textColor()); // set friday with red mark
                            } else {
                                $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, Carbon::parse($objData[$schema[$i][$j]])->format('D, d M Y')); // set normal date
                            }

                        } elseif($schema[$i][$j] == 'DestinationCode') {
                            //Set Destination Code
                            $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]])->getStyle(chr($asciiValue+$j).$startCellCoordinate)->getAlignment()->applyFromArray(['horizontal'   => Alignment::HORIZONTAL_LEFT]);
                        }  else {
                            //Set Client Name in all Worksheet
                            //echo $objData[$schema[$i][$j]].'<br>';
                            $this->outgoingExcel->getActiveSheet()->setCellValue(chr($asciiValue+$j).$startCellCoordinate, $objData[$schema[$i][$j]]);
                        }
                    }
                }

                if($keyCount == (count($queries[$i]) - 1)) {
                    $coordinateRange        = 'A'.$this->tableDataCoordinate.':'.chr($endIndexValue).(count($queries[$i])+$this->tableHeaderCoordinate);
                    $totalQueryValueCount   = (count($queries[$i])+8);
                    $LastCoordinateRange    = 'A'.$totalQueryValueCount.':'.chr($endIndexValue).$totalQueryValueCount;
                    $isSummationWorksheet   = array(0,1,2);

                    //Worksheet-1
                    if(in_array($i, $isSummationWorksheet)) {
                        //echo $i;
                        //echo count($schema[$i]);
                        $this->outgoingExcel->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->table2());
                        $this->outgoingExcel->getActiveSheet()
                            ->setCellValue('A'.($totalQueryValueCount), 'Total:')
                            ->getStyle($LastCoordinateRange)
                            ->applyFromArray($this->footer());
                        if($i == 0) {
                            $this->outgoingExcel->getActiveSheet()->setCellValue('C'.$totalQueryValueCount, '=SUM(C'.$this->tableDataCoordinate.':C'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('C'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/C'.$totalQueryValueCount)->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=C'.$totalQueryValueCount.'/C'.$totalQueryValueCount)->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                        } elseif($i == 1) {
                            $this->outgoingExcel->getActiveSheet()->setCellValue('D'.$totalQueryValueCount, '=SUM(D'.$this->tableDataCoordinate.':D'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('D'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('E'.$totalQueryValueCount, '=SUM(E'.$this->tableDataCoordinate.':E'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('E'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=E'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumber00());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=D'.$totalQueryValueCount.'/D'.$totalQueryValueCount)->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('H'.$totalQueryValueCount, '=E'.$totalQueryValueCount.'/E'.$totalQueryValueCount)->getStyle('H'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatPercentage());
                        } else {
                            $this->outgoingExcel->getActiveSheet()->setCellValue('F'.$totalQueryValueCount, '=SUM(F'.$this->tableDataCoordinate.':F'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('F'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('G'.$totalQueryValueCount, '=SUM(G'.$this->tableDataCoordinate.':G'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('G'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                            $this->outgoingExcel->getActiveSheet()->setCellValue('H'.$totalQueryValueCount, '=SUM(H'.$this->tableDataCoordinate.':H'.(count($queries[$i])+$this->tableHeaderCoordinate).')')->getStyle('H'.$totalQueryValueCount)->getNumberFormat()->applyFromArray($this->FormatNumberComma());
                        }
                    } else {
                        $this->outgoingExcel->getActiveSheet()->getStyle($coordinateRange)->applyFromArray($this->table());
                    }
                }
            }
        }
        //Add Chart
        $this->iosOGChartOne($iosQueryResultCount);
        $this->iosOGChartTwo($iosQueryResultCount);
        $this->dailyChartRender($this->outgoingExcel, 4, $dailyQueryResultCount, 'IGW OG Minute');
        return true;
    }

    //Incoming Worksheet Chart

    /**
     * @param $result
     * @return Chart
     * @throws Exception
     */
    private function incomingANSChartOne($result): Chart
    {
        $incomingANSWorksheet = $this->incomingExcel->setActiveSheetIndex(3); //Get Active Worksheet

        $headerCoordinate = $this->tableHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $result+$headerCoordinate;
        $chartTopLeftPosition = 'A'.($startingCoordinate+4);
        $chartBottomRightPosition = 'F'.(($startingCoordinate+4)+12);
        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate;

        $seriesCategoryRange = 'ANS!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange = 'ANS!$C$'.$seriesValueTopPoint.':$C$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING,'ANS!$B$7', null, 1),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange, null, 4),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange, null, 4),
        ];

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_PIECHART_3D, // plotType
            null, // plotGrouping (Pie charts don't have any grouping)
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues // plotValues
        );

        //Set up a layout object for the Pie chart
        $layout = new Layout();
        //$layout->setShowVal(true);
        $layout->setShowPercent(true);
        $layout->setShowLeaderLines(true);

        //  Set the series in the plot area
        $plotArea = new PlotArea($layout, [$series]);
        //  Set the chart legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        $title = new Title('Successful Calls');

        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            $title,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,         // displayBlanksAs
            'zero',      // displayBlanksAs
            null,       // xAxisLabel
            null        // yAxisLabel    - Pie charts don't have a Y-Axis
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $incomingANSWorksheet->addChart($chart);
        //return $chart;
    }

    /**
     * @param $result
     * @return Chart
     * @throws Exception
     */
    private function incomingANSChartTwo($result): Chart
    {
        $incomingANSWorksheet = $this->incomingExcel->setActiveSheetIndex(3); //Get Active Worksheet
        $get_D_index = 71; //71 is G ascii value
        for($a = $get_D_index; $a <= 76; $a++) { //71 is G ascii value;
            $this->incomingExcel->getActiveSheet()->getColumnDimension(chr($a))->setWidth(20);
        }
        $headerCoordinate = $this->tableHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $result+$headerCoordinate;
        $chartTopLeftPosition = 'G'.($startingCoordinate+4);
        $chartBottomRightPosition = 'K'.(($startingCoordinate+4)+12);
        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate;

        $seriesCategoryRange = 'ANS!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange = 'ANS!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING,'ANS!$B$7', null, 1),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange, null, 4),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange, null, 4),
        ];

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_PIECHART_3D, // plotType
            null, // plotGrouping (Pie charts don't have any grouping)
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues // plotValues
        );

        //Set up a layout object for the Pie chart
        $layout = new Layout();
        //$layout->setShowVal(true);
        $layout->setShowPercent(true);
        $layout->setShowLeaderLines(true);


        //  Set the series in the plot area
        $plotArea = new PlotArea($layout, [$series]);
        //  Set the chart legend
        $legend = new Legend(Legend::XL_LEGEND_POSITION_TOP, null, false);

        $title = new Title('Duration');

        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            $title,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,         // displayBlanksAs
            'zero',      // displayBlanksAs
            null,       // xAxisLabel
            null        // yAxisLabel    - Pie charts don't have a Y-Axis
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $incomingANSWorksheet->addChart($chart);
        //return $chart;
    }

    //Daily Duration Chart
    private function dailyChartRender($worksheet, $sheetNumber, $totalData, $title) {
        $chartRender = $worksheet->setActiveSheetIndex($sheetNumber)->setShowGridlines(false);
        /*
        $sheet->getDefaultStyle()->applyFromArray(
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

        //Get Active Worksheet
        //SERIES(Daily!$D$6,Daily!$B$7:$B$704,Daily!$D$7:$D$704,1)
        $headerCoordinate = $this->tableHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $totalData;

        $chartTopLeftPosition = 'D3'; //chart area starting point
        $chartBottomRightPosition = 'S'.($startingCoordinate-12); //Chart area ending point

        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate+$this->tableHeaderCoordinate;

        $seriesCategoryRange = 'Daily!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange = 'Daily!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING,'Daily!$B$7', null, 1),
            //new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$2:$A$5', null, 4),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $seriesCategoryRange, null, 4),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $seriesValueRange, null, 4),
        ];

        //Build the dataseries
        $series = new DataSeries (
            DataSeries::TYPE_BARCHART, // plotType
            DataSeries::GROUPING_STANDARD, // plotGrouping
            range(0, count($dataSeriesValues) - 1), // plotOrder
            $dataSeriesLabels, // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues // plotValues
        );

        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        //Set up a layout object for the Pie chart
        $layout = new Layout();
        // //$layout->setShowVal(true);
        // $layout->setShowPercent(true);
        // $layout->setShowLeaderLines(true);

        //  Set the series in the plot area
        $plotArea = new PlotArea(null, [$series]);
        //  Set the chart legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        $title = new Title($title);
        $xAxisLabel = new Title('Date');
        $yAxisLabel = new Title('Minute');
        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            $title,     // title
            //$legend,    // legend
            null,
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,         // displayBlanksAs
            'zero',      // displayBlanksAs new value
            $xAxisLabel,       // xAxisLabel
            $yAxisLabel // yAxisLabel
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $chartRender->addChart($chart); //render
    }

    //Incoming Worksheet Chart

    /**
     * @param $result
     * @return Chart
     * @throws Exception
     */
    private function iosOGChartOne($result): Chart
    {
        $chartRender = $this->outgoingExcel->setActiveSheetIndex(1); //Get Active Worksheet

        $headerCoordinate = $this->tableHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $result+$headerCoordinate;
        $chartTopLeftPosition = 'A'.($startingCoordinate+4);
        $chartBottomRightPosition = 'H'.(($startingCoordinate+4)+16);

        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate;

        $seriesCategoryRange = 'IOS!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange = 'IOS!$E$'.$seriesValueTopPoint.':$E$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING,'IOS!$B$7', null, 1),
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
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);

        $title = new Title('Successful Calls');

        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            //$title,     // title
            null,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,         // displayBlanksAs
            'zero',      // displayBlanksAs new value
            null,       // xAxisLabel
            null        // yAxisLabel    - Pie charts don't have a Y-Axis
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area

        return $chartRender->addChart($chart);
        //return $chart;
    }

    //Incoming Worksheet Chart

    /**
     * @param $result
     * @return Chart
     * @throws Exception
     */
    private function iosOGChartTwo($result): Chart
    {
        $chartRender = $this->outgoingExcel->setActiveSheetIndex(1); //Get Active Worksheet

        $headerCoordinate = $this->tableHeaderCoordinate; //Report Header Coordinate value
        $startingCoordinate = $result+$headerCoordinate;
        $chartTopLeftPosition = 'A'.($startingCoordinate+22);
        $chartBottomRightPosition = 'H'.(($startingCoordinate+22)+14);

        //Series Values
        $seriesValueTopPoint = ($headerCoordinate+1);
        $seriesValueBottomPoint = $startingCoordinate;

        $seriesCategoryRange1 = 'IOS!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesCategoryRange2 = 'IOS!$B$'.$seriesValueTopPoint.':$B$'.$seriesValueBottomPoint;
        $seriesValueRange1 = 'IOS!$D$'.$seriesValueTopPoint.':$D$'.$seriesValueBottomPoint;
        $seriesValueRange2 = 'IOS!$E$'.$seriesValueTopPoint.':$E$'.$seriesValueBottomPoint;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'IOS!$D$7', null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'IOS!$E$7', null, 1),
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
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        //$title = new Title('IOS');
        //$yAxisLabel = new Title('Value');
        //$xaxis = new Axis();
        //$xaxis->setAxisOptionsProperties('low', null, null, null, null, null, 0, 0, null, null);
        //  Create the chart
        $chart = new Chart (
            'chart',    // name
            null,     // title
            $legend,    // legend
            $plotArea,  // plotArea
            true,       // plotVisibleOnly
            //0,         // displayBlanksAs
            'zero',      // displayBlanksAs new value
            null,       // xAxisLabel
            //$yAxisLabel  // yAxisLabel
            null
        );

        //Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition($chartTopLeftPosition);
        $chart->setBottomRightPosition($chartBottomRightPosition); //Add 16 for cover whole plot area
        //dd($chart);
        return $chartRender->addChart($chart);
        //return $chart;
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

    //Incoming report generate

    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @param string|null $directory
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function Incoming($inputFromDate, $inputToDate, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->incomingExcel);
        $this->CreateIncomingWorksheet($inputFromDate, $inputToDate);
        $this->IncomingHeading();
        $this->IncomingDataSetter($inputFromDate, $inputToDate);

        //Default Active Worksheet 0
        $this->incomingExcel->setActiveSheetIndex(0);
        $filename = Carbon::parse($inputToDate)->format('d-M-Y').' Incoming Call Status';

        $writer = new Xlsx($this->incomingExcel);
        $writer->setIncludeCharts(true);

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/callsummary/'.$filename.'.xlsx');
        }

        return true;
    }
    //Incoming Chart End

    //Outgoing report generate
    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @param null $additionalWorksheet
     * @param string|null $directory
     * @param bool $scheduleGenerateType
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function Outgoing($inputFromDate, $inputToDate, $additionalWorksheet=null, string $directory = null, bool $scheduleGenerateType = false): bool
    {
        $this->authorInfo($this->outgoingExcel);
        $this->CreateOutgoingWorksheet($inputFromDate, $inputToDate, $additionalWorksheet);
        $this->OutgoingHeading();
        $this->OutgoingDataSetter($inputFromDate, $inputToDate);

        //Default Active Worksheet 0
        $this->outgoingExcel->setActiveSheetIndex(0);
        $filename = Carbon::parse($inputToDate)->format('d-M-Y').' Outgoing Call Status';
        $writer = new Xlsx($this->outgoingExcel);
        $writer->setIncludeCharts(true);

        if($scheduleGenerateType) {
            $writer->save(public_path().$directory.$filename.'.xlsx');
        } else {
            $writer->save(public_path().'/platform/igw/callsummary/'.$filename.'.xlsx');
        }

        return true;
    }

    public function dataAttachedInMailBody($inputFromDate, $inputToDate)
    {
        //$inputFromDate = '20231206';
        //$inputToDate = '20231206';

        $fromDate   = $inputFromDate.' 00:00:00';
        $toDate     = $inputToDate.' 23:59:59';

        //Report comparison date and queries
        $sevenBeforeFromDate    = Carbon::parse($inputFromDate)->subDays(7)->format('Ymd').' 00:00:00';
        $sevenBeforeToDate      = Carbon::parse($inputToDate)->subDays(7)->format('Ymd').' 23:59:59';
        $oneBeforeFromDate      = Carbon::parse($inputFromDate)->subDays()->format('Ymd').' 00:00:00';
        $oneBeforeToDate        = Carbon::parse($inputToDate)->subDays()->format('Ymd').' 23:59:59';

        //Get previous day report
        $sevenDayBefore = $this->getSolidArray(CallSummaryIncomingQuery::dayWiseIncoming($sevenBeforeFromDate, $sevenBeforeToDate)->toArray(),1); //Seven (7) day before query
        $oneDayBefore   = $this->getSolidArray(CallSummaryIncomingQuery::dayWiseIncoming($oneBeforeFromDate, $oneBeforeToDate)->toArray(), 1); //Seven (7) day before query

        //Report day queries
        $dayWiseIN       = $this->getSolidArray(CallSummaryIncomingQuery::dayWiseIncoming($fromDate, $toDate)->toArray(), 2);
        $dayWiseOG       = $this->getSolidArray(CallSummaryOutgoingQuery::dayWiseOutgoing($fromDate, $toDate)->toArray(), 2);

        $dayWiseIN_2       = $this->getSolidArray(CallSummaryIncomingQuery::dayWiseIncoming($fromDate, $toDate)->toArray(), 1);

        //dump(current($dayWiseIN_2));
        //dd(current(array_slice($sevenDayBefore,2)));

        $dataArray = array($sevenDayBefore, $oneDayBefore, $dayWiseIN_2);

        $comparisonTableHeading = [];
        $comparisonTableValue = [];
        for($i = 0; $i < count($dataArray); $i++) {
            foreach ($dataArray[$i] as $key => $value) {
                if(in_array($key, (array)'traffic_date')) {
                    $comparisonTableHeading[] = $value;
                }

                if(in_array($key, (array)'Duration')) {
                    $comparisonTableValue[] = $value;
                }
            }
        }

        //dump($comparisonTableHeading);
        //dump($comparisonTableValue);

        //Wrapper table :: incoming and outgoing summary
        $tableContent = "<table style='border-collapse: collapse; font-size: 15px;'>";

        $tableContent .= "<tr>";

        //Incoming call summary
        $tableContent .= "<td>";

            //Incoming content
            $tableContent .= "<p style='margin-bottom: 8px; font-weight: bold;'><u>Incoming Call Summary:</u></p>";
            $tableContent .= "<table border='1' style='border-collapse: collapse; font-size: 13px; width: 300px;'>"; //Inner table 1
                foreach($dayWiseIN as $key => $value) {
                    $tableContent .= "<tr>";
                    $tableContent .= "<td style='text-align: center;'>";
                    if($key === 'SuccessfulCall') {
                        $tableContent .= 'Successful Calls';
                    } elseif($key === 'Duration') {
                        $tableContent .= 'Total Duration (min)';
                    } else {
                        $tableContent .= 'ACD (min)';
                    }
                    $tableContent .= "</td>";
                    $tableContent .= "<td style='text-align: right; padding-right: 5px;'>";
                    if($key !== 'ACD') {
                        $tableContent .= number_format($value, 0, '.', ',');
                    } else {
                        $tableContent .= number_format($value, 2);
                    }
                    $tableContent .= "</td>";
                    $tableContent .= "</tr>";
                }
            $tableContent .= "</table>";  //End inner table 1

        $tableContent .= "</td>"; //End 1st td
        $tableContent .= "<td width='10'></td>";
        //Outgoing call summary
        $tableContent .= "<td>";
            $tableContent .= "<p style='margin-bottom: 8px; font-weight: bold;'><u>Outgoing Call Summary:</u></p>";
            $tableContent .= "<table border='1' style='border-collapse: collapse; font-size: 13px; width: 300px;'>";  //Inner table 2
            foreach($dayWiseOG as $key => $value) {
                $tableContent .= "<tr>";
                $tableContent .= "<td style='text-align: center;'>";
                if($key === 'SuccessfulCall') {
                    $tableContent .= 'Successful Calls';
                } elseif($key === 'Duration') {
                    $tableContent .= 'Total Duration (min)';
                } else {
                    $tableContent .= 'ACD (min)';
                }
                $tableContent .= "</td>";
                $tableContent .= "<td style='text-align: right; padding-right: 5px;'>";
                if($key !== 'ACD') {
                    $tableContent .= number_format($value, 0, '.', ',');
                } else {
                    $tableContent .= number_format($value, 2);
                }
                $tableContent .= "</td>";
                $tableContent .= "</tr>";
            }
            $tableContent .= "</table>"; //End inner table 2

        $tableContent .= "</td>";

        $tableContent .= "</tr>";
        $tableContent .= "</table>"; //End wrapper table

        //Comparison table :: wrapper table
        $tableContent .= "<table style='border-collapse: collapse;'>";

        $tableContent .= "<tr><td height='30' colspan='2'></td></tr>";
        $tableContent .= "<tr>";
            $tableContent .= "<td colspan='2' style='font-weight: bold;'><u>Incoming duration comparison " . $comparisonTableHeading[2] . ' vs ' . $comparisonTableHeading[1] ." and " . $comparisonTableHeading[2] . ' vs ' . $comparisonTableHeading[0] . "</u></td>";
        $tableContent .= "</tr>";

        $tableContent .= "<tr><td height='8' colspan='2'></td></tr>";

        $tableContent .= "<tr>";
            $tableContent .= "<td colspan='2'>";
            $tableContent .= "<table border='1' style='border-collapse: collapse; text-align: center; font-size: 13px; width: 900px;'>"; // inner table

            $tableContent .= "<tr>";
            $tableContent .= "<td>Date</td>";
            $tableContent .= "<td>" . $comparisonTableHeading[0] . " (Last Week) </td>";
            $tableContent .= "<td>" . $comparisonTableHeading[1] . " (Yesterday) </td>";
            $tableContent .= "<td>" . $comparisonTableHeading[2] . " (Today) </td>";
            $tableContent .= "<td>Diff (" . $comparisonTableHeading[2] . ' - ' . $comparisonTableHeading[1] . ") </td>";
            $tableContent .= "<td>Diff (" . $comparisonTableHeading[2] . ' - ' . $comparisonTableHeading[0] . ") </td>";
            $tableContent .= "</tr>";

            $tableContent .= "<tr>";

            $tableContent .= "<td>Dur (Min)</td>";
            $tableContent .= "<td>". number_format($comparisonTableValue[0],0,'.', ',') ."</td>";
            $tableContent .= "<td>". number_format($comparisonTableValue[1],0,'.', ',') ."</td>";
            $tableContent .= "<td>". number_format($comparisonTableValue[2],0,'.', ',') ."</td>";


        $getPercentageValue1 = $this->calculatePercentageChange($comparisonTableValue[1],$comparisonTableValue[2]);
        //dump($getPercentageValue1);

        if($getPercentageValue1 >= 0) {
            //Increase
            $valueChanged1 = "<span style='color:green;'> ▲" . number_format($getPercentageValue1,2,'.',',') . "%</span>";
        } else {
            $valueChanged1 = "<span style='color:red;'> ▼" . number_format($getPercentageValue1,2,'.',',') . "%</span>";
        }

        $getPercentageValue2 = $this->calculatePercentageChange($comparisonTableValue[0],$comparisonTableValue[2]);
        //dump($getPercentageValue2);

        if($getPercentageValue2 >= 0) {
            //Increase
            $valueChanged2 = "<span style='color:green;'> ▲" . number_format($getPercentageValue2,2,'.',',') . "%</span>";
        } else {
            $valueChanged2 = "<span style='color:red;'> ▼" . number_format($getPercentageValue2, 2, '.', ',') . "%</span>";
        }

        //dd($getPercentageValue2);
        $tableContent .= "<td>". number_format(($comparisonTableValue[2]-$comparisonTableValue[1]),0,'.', ',') . " " . $valueChanged1 ."</td>";
        $tableContent .= "<td>". number_format(($comparisonTableValue[2]-$comparisonTableValue[0]),0,'.', ',') . " " . $valueChanged2 ."</td>";
        $tableContent .= "</tr>";
        $tableContent .= "</table>"; //End inner table
        $tableContent .= "</td></tr>";
        $tableContent .= "</table>"; //End wrapper table

        return [
            'tableContent' => $tableContent
        ];
        //return view('emails.igw-call-summary-report', compact('tableContent', ));
    }

    public function calculatePercentageChange($oldValue, $newValue) {
        if ($oldValue == 0) {
            // To avoid division by zero if the old value is zero
            return "Undefined (division by zero)";
        }
        return (($newValue - $oldValue) / abs($oldValue)) * 100;
    }

    /**
     * @param array $data
     * @param $findValue
     * @return mixed|void
     */
    public function getFirst(array $data, $findValue) {
        foreach ($data as $key => $value) {
            if(in_array($key, $findValue)) {
                return $value;
            }
        }
    }


    /**
     * @param array $data
     * @param $removeNumberElement
     * @return array
     */
    public function getSolidArray(array $data, $removeNumberElement): array
    {
        return array_slice(get_object_vars((object) $data[0]), $removeNumberElement);
    }

    //Sticky summary table
    /**
     * @param $inputFromDate
     * @param $inputToDate
     * @return bool
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function stickySummary($inputFromDate, $inputToDate): bool
    {
        $this->authorInfo($this->screenshot); //Author information setting
        $fromDate   = $inputFromDate.' 00:00:00';
        $toDate     = $inputToDate.' 23:59:59';

        //Report comparison date and queries
        $sevenBeforeFromDate    = Carbon::parse($inputFromDate)->subDays(7)->format('Ymd').' 00:00:00';
        $sevenBeforeToDate      = Carbon::parse($inputToDate)->subDays(7)->format('Ymd').' 23:59:59';
        $oneBeforeFromDate      = Carbon::parse($inputFromDate)->subDays()->format('Ymd').' 00:00:00';
        $oneBeforeToDate        = Carbon::parse($inputToDate)->subDays()->format('Ymd').' 23:59:59';

        //Get previous day report
        $sevenDayBefore = CallSummaryIncomingQuery::dayWiseIncoming($sevenBeforeFromDate, $sevenBeforeToDate); //Seven (7) day before query
        $oneDayBefore   = CallSummaryIncomingQuery::dayWiseIncoming($oneBeforeFromDate, $oneBeforeToDate); //Seven (7) day before query

        //Report day queries
        $dayWiseIN       = CallSummaryIncomingQuery::dayWiseIncoming($fromDate, $toDate);
        $dayWiseOG       = CallSummaryOutgoingQuery::dayWiseOutgoing($fromDate, $toDate);

        //Initialization
        $heading    = array('Incoming Call Summary:', 'Outgoing Call Summary:');
        $titles     = array('Successful Calls', 'Total Duration (min)', 'ACD (min)');
        $schema     = array('SuccessfulCall', 'Duration', 'ACD');
        $queries    = array($dayWiseIN, $dayWiseOG);

        //Reports date
        $reportDate = Carbon::parse($inputToDate)->format('d M Y');
        //Auto size
        foreach(range('C','J') as $index) {
            $this->screenshot->getActiveSheet()->getColumnDimension($index)->setAutoSize(true);
        }

        //Top report date echo, don't use it in your mail body
        $this->screenshot->getActiveSheet()->setCellValue('C1', 'Report date: '.$reportDate)->mergeCells('C1:D1')->getStyle('C1:D1')->applyFromArray($this->backgroundWithBorder('ffd100'));

        //Summary screenshot table create
        for($i = 0; $i < count($heading); $i++) {
            $this->screenshot->getActiveSheet()->setCellValue( $i==0 ? 'C4':'F4', $heading[$i])->getStyle( $i==0 ? 'C4':'F4')->applyFromArray($this->summaryStickyHeadingDesign());

            //Title echo
            foreach($titles as $key => $title) {
                $titleCellIndex = $i==0 ? 'C'.(6+$key):'F'.(6+$key);
                $this->screenshot->getActiveSheet()
                    ->setCellValue($titleCellIndex, $title)
                    ->getStyle($titleCellIndex)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            //Value echo
            foreach($queries[$i] as $query) {
                $data = get_object_vars($query);
                for($j = 0; $j < count($schema); $j++) {
                    $cellIndex = $i == 0 ? 'D'.(6+$j):'G'.(6+$j);
                    $this->screenshot->getActiveSheet()
                        ->setCellValue($cellIndex, $data[$schema[$j]])
                        ->getStyle($cellIndex)
                        ->getNumberFormat()
                        ->applyFromArray($schema[$j] == 'ACD' ? $this->FormatNumber00(): $this->FormatNumberComma());
                }
                //Design table
                $this->screenshot->getActiveSheet()->getStyle($i == 0 ? 'C6:D8': 'F6:G8')->applyFromArray($this->summaryStickyDesign());
            }
        }

        //Comparison queries
        $comparisonReportDate1 = Carbon::parse($inputToDate)->subDays(7)->format('d M y'); //Format (14 Jan 2020)
        $comparisonReportDate2 = Carbon::parse($inputToDate)->subDays()->format('d M y'); //Format (14 Jan 2020)
        $comparisonReportDate3 = Carbon::parse($inputToDate)->format('d M y'); //Format (14 Jan 2020), Main report date

        //Query container
        $comparisonQueries = array($sevenDayBefore,$oneDayBefore, $dayWiseIN);
        //Title container
        $comparisonTitles = array(
            $comparisonReportDate1.' (Last Week)',
            $comparisonReportDate2.' (Yesterday)',
            $comparisonReportDate3.' (Today)',
            'Diff('.$comparisonReportDate3.' - '.$comparisonReportDate2.')',
            'Diff('.$comparisonReportDate3.' - '.$comparisonReportDate1.')'
        );

        //Incoming duration comparison
        $this->screenshot->getActiveSheet()->setCellValue('J1', 'Duration Comparison')->getStyle('J1')->applyFromArray(['font' => ['color' => ['rgb' => 'ffffff']]]); //Text white color, auto resize not working in this cell index, so use dummy content here

        //Heading echo
        ////Incoming duration comparison 13 Jan 20 vs. 12 Jan 20 and 13 Jan 20 vs. 06 Jan 20
        $this->screenshot->getActiveSheet()->setCellValue('C11', 'Incoming duration comparison '.$comparisonReportDate3.' vs '.$comparisonReportDate2.' and '.$comparisonReportDate3.' vs '.$comparisonReportDate1.': ')->mergeCells('C11:J11')->getStyle('C11:J11')->applyFromArray($this->summaryStickyHeadingDesign());
        $this->screenshot->getActiveSheet()->setCellValue('C13', 'Date')->getStyle('C13')->applyFromArray($this->summaryStickyDesign());
        $this->screenshot->getActiveSheet()->setCellValue('C14', 'Dur(Min)')->getStyle('C14')->applyFromArray($this->summaryStickyDesign());

        //Comparison titles
        for($i = 0; $i < count($comparisonTitles); $i++) {
            //C-67, D-68 ascii value
            $titleCellIndex = chr(68+$i).'13'; //Title starting index and increase 1 until loop iteration end
            $durationIndex = chr(68+$i).'14'; //Duration starting index and increase 1 until loop iteration end

            if($i <= 2) {
                //Report date echo
                $this->screenshot->getActiveSheet()
                    ->setCellValue($titleCellIndex, $comparisonTitles[$i])
                    ->getStyle($titleCellIndex)
                    ->getNumberFormat()
                    ->applyFromArray([
                        'formatCode' => NumberFormat::FORMAT_DATE_XLSX15_2
                    ]);

                //Day wise duration echo
                foreach($comparisonQueries[$i] as $value) {
                    $this->screenshot->getActiveSheet()
                        ->setCellValue($durationIndex, $value->Duration)
                        ->getStyle($durationIndex)
                        ->getNumberFormat()
                        ->applyFromArray($this->FormatNumberComma());
                }
            } else {
                //echo $comparisonTitles[$i].'<br>';
                $minusFormula = $i == 3 ? '=F14-E14' : '=F14-D14';
                $percentageFormula = $i == 3 ? '=(F14-E14)/F14' : '=(F14-D14)/F14';
                $compareValueCell = $i == 3 ? 'G14':'I14';
                $percentageValue = $i == 3 ? 'H14':'J14';
                $mergeTitleCell = $i == 3 ? 'G13:H13':'I13:J13';

                //Comparison value title
                $this->screenshot->getActiveSheet()
                    ->setCellValue($i == 3 ? 'G13':'I13', $comparisonTitles[$i])
                    ->mergeCells($mergeTitleCell)
                    ->getStyle($mergeTitleCell);

                //Comparison value
                $this->screenshot->getActiveSheet()
                    ->setCellValue($compareValueCell, $minusFormula)
                    ->getStyle($compareValueCell)
                    ->getNumberFormat()
                    ->applyFromArray($this->FormatNumberComma());

                //Percentage calculate and formatting
                $this->screenshot->getActiveSheet()
                    ->setCellValue($percentageValue, $percentageFormula)
                    ->getStyle($percentageValue)
                    ->getNumberFormat()
                    ->applyFromArray([
                        'formatCode' => NumberFormat::FORMAT_ARROW
                    ]);
            }

            //Table style setting
            $this->screenshot->getActiveSheet()->getStyle('D13:J13')->applyFromArray($this->backgroundWithBorder('ffffff'));
            $this->screenshot->getActiveSheet()->getStyle('D13:F14')->applyFromArray($this->backgroundWithBorder('ffffff'));
            $this->screenshot->getActiveSheet()->getStyle('G14:H14')->applyFromArray($this->backgroundWithOutlineBorder('ffffff'));
            $this->screenshot->getActiveSheet()->getStyle('I14:J14')->applyFromArray($this->backgroundWithOutlineBorder('ffffff'));

        }
        //dd('end');

        //Default Active Worksheet 0
        $this->screenshot->setActiveSheetIndex(0);
        $filename = Carbon::parse($inputToDate)->format('d-M-Y').' screenshot summary';
        $writer = new Xlsx($this->screenshot);
        $writer->save(public_path().'/platform/igw/callsummary/'.$filename.'.xlsx');
        return true;
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reports(): RedirectResponse
    {
        //Validation
        request()->validate([
            'reportTypes'  => 'required|numeric',
            'reportDate'   => 'required',
            'previousDays' => 'nullable'
        ]);

        $reportTypes            = request()->reportTypes;
        $additionalWorksheet    = request()->previousDays;
        $inputFromDate          = Carbon::parse(request()->reportDate)->format('Ymd');
        $inputToDate            = Carbon::parse(request()->reportDate)->format('Ymd');

        $processStartTime = microtime(TRUE);

        if($reportTypes == 3) {
            $incoming   = $this->Incoming($inputFromDate, $inputToDate);
            $outgoing   = $this->Outgoing($inputFromDate, $inputToDate, $additionalWorksheet);
            $summary    = $this->stickySummary($inputFromDate, $inputToDate);
        } elseif($reportTypes == 2) {
            $outgoing = $this->Outgoing($inputFromDate, $inputToDate, $additionalWorksheet);
        } else {
            $incoming = $this->Incoming($inputFromDate, $inputToDate);
        }

        //Disconnect Worksheets from memory
        $this->incomingExcel->disconnectWorksheets();
        $this->outgoingExcel->disconnectWorksheets();
        $this->screenshot->disconnectWorksheets();
        unset($this->incomingExcel);
        unset($this->outgoingExcel);
        unset($this->screenshot);

        $processEndTime = microtime(TRUE);
        $executionTime = round(($processEndTime - $processStartTime),4);

        if(isset($incoming) || isset($outgoing) || isset($summary)) {
            return Redirect::to('platform/igw/report/callsummary/')->with('success',"Report generated! Process execution time: $executionTime Seconds");
        } else {
            return Redirect::to('platform/igw/report/callsummary/')->with('danger','Something went wrong');
        }
    }

    public function index() {
        $getFiles = Storage::disk('public')->files('platform/igw/callsummary/');
        $files = array();

        foreach ($getFiles as $file) {
            $fileData = explode("/", $file);
            array_push($files, end($fileData));
        }

        return view('platform.igw.index', compact('files'));
    }

    //Download IOS Daily Comparison Report

    /**
     * @param $filename
     * @return BinaryFileResponse
     */
    public function getFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/igw/callsummary/'.$filename;
        return response()->download($file);
    }

    //Delete Generated Report

    /**
     * @param $filename
     * @return RedirectResponse
     */
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('public')->delete('/platform/igw/callsummary/'.$filename);
        return Redirect::to('platform/igw/report/callsummary')->with('success','Report Successfully Deleted');
    }

    //Zip Download
    public function zipCreator() {
        $date = 'IGW Report '. Carbon::now()->subdays()->format('d-M-Y');
        $zip_file =  public_path(). '/platform/igw/ZipFiles/callsummary/'.$date.'.zip'; //Store all created zip files here
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $path = public_path(). '/platform/igw/callsummary';

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
            return Redirect::to('platform/igw/report/callsummary')->with('danger','Directory is empty. Please generate reports');
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
        $clean1 = Storage::disk('public')->delete(Storage::disk('public')->files('platform/igw/callsummary/'));
        if($clean1) {
            return Redirect::to('platform/igw/report/callsummary/')->with('success','All Reports Successfully Deleted');
        } else {
            return Redirect::to('platform/igw/report/callsummary/')->with('danger','There is are problem to delete files');
        }
    }

}
