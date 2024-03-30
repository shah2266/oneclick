<?php

namespace App\Http\Controllers\IOS;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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

class BtrcController extends Controller
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
    /**
     * @return array[]
     */
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

    /**
     * @return array[]
     */
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
                'name' => 'Calibri',
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

    /**
     * @return \array[][]
     */
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
     * IOS Call Summary Reports for BTRC
     */

    //View loading

    public function index() {
        $lastModified = Storage::disk('public')->lastModified("/platform/ios/btrc/Daily BTrac IOS Call Report for BTRC.xlsx");
        $fileLastModifiedDate = Carbon::createFromTimeStampUTC($lastModified,'Asia/Dhaka')->format('Ymd');
        $nowDate = Carbon::NOW()->format('Ymd');
        $fileName = 'Daily BTrac IOS Call Report for BTRC.xlsx';
        return view('platform.ios.iosBtrc', compact('fileLastModifiedDate','nowDate', 'fileName'));
    }

    //Download IOS Daily Report
    public function downloadFile($filename): BinaryFileResponse
    {
        $file = public_path(). '/platform/ios/btrc/'.$filename;
        return response()->download($file);
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function reportGenerate(Request $request): RedirectResponse
    {

        //Validation check
        $this->validate($request, [
            'reportDate' => 'required',
        ]);

        //Input Date formatting
        $fromDate = Carbon::parse($request->reportDate)->subDays(9)->format('Ymd'); //Getting previous days upto 9
        $toDate = Carbon::parse($request->reportDate)->format('Ymd'); //Input date
        //$dayAfter = date('Y-m-d', strtotime("-10 days"));

        //DB MSSQL Query Execution
        $iosDayWise = DB::connection('sqlsrv2')->table('CallSummary as cm')
                    ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("SUM(cm.SuccessfulCall) as successfulCall"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
                    ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
                    ->where('cm.ReportTrafficDirection','=',1)
                    ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                    ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),'ASC')
                    ->get();


        /**
         * Excel file generating
         */
        //Database column name
        //$db_schema_arr = array('trafficDate','successfulCall','duration');
        $cellIndexArr = array('D','E','F','G','H','I','J','K','L','M','N');

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

        //Worksheet 0
        $this->spreadsheet->setActiveSheetIndex(0);
        $this->spreadsheet->getActiveSheet()->setTitle("IOS Call Report for BTRC");

        $this->spreadsheet->getActiveSheet()
                            ->getStyle('D5:N6')
                            ->applyFromArray($this->headingTopStyle());

        $this->spreadsheet->getActiveSheet()
                            ->getStyle('D5:N7')
                            ->applyFromArray($this->allBorderStyle());

        $this->spreadsheet->getActiveSheet()
                            ->getStyle('E7:N7')
                            ->applyFromArray($this->tableBodyStyle());

        $this->spreadsheet->getActiveSheet()
                            ->setCellValue('D5', 'Date')
                            ->mergeCells('D5:N5');

        $this->spreadsheet->getActiveSheet()
                            ->setCellValue('D6', 'IOS Name');

        $this->spreadsheet->getActiveSheet()
                            ->setCellValue('D7', 'Bangla Trac (IOS)')
                            ->getStyle('D7')
                            ->applyFromArray($this->headingTopStyle());

        //Auto Cell Sizing
        foreach($cellIndexArr as $cellIndex) {
            $this->spreadsheet->getActiveSheet()->getColumnDimension($cellIndex)->setAutoSize(true);
        }

        //dd($iosDayWise);
        for($i = 1; $i <= 1; $i++) {
            for($j = 0+$i; $j <= 10; $j++){

                $this->spreadsheet->getActiveSheet()
                                  ->setCellValue($cellIndexArr[$j].'6', Carbon::parse($iosDayWise[$j-$i]->trafficDate)->format('d-M'));

                $this->spreadsheet->getActiveSheet()
                                  ->setCellValue($cellIndexArr[$j].'7', $iosDayWise[$j-$i]->duration)
                                  ->getStyle('E7:N7')
                                  ->getNumberFormat()
                                  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA);
            }
        }


        //dd($iosDayWise);

        $writer = new Xlsx($this->spreadsheet);

        //Filename
        $filename = 'Daily BTrac IOS Call Report for BTRC';

        //File Stored Directory
		//$writer->save('C:/Users/User-PC/Desktop/IOSDailyReport/btrc/'.$filename.'.xlsx'); //This pc directory
        //$writer->save('C:/Users/shah.alam/Desktop/IOSDailyReport/btrc/'.$filename.'.xlsx'); //Shah Alam pc directory
        $writer->save(public_path().'/platform/ios/btrc/'.$filename.'.xlsx');

        //Disconnect Worksheets from memory
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        $btrcFileName = 'Daily BTrac IOS Call Report for BTRC.xlsx';
        return Redirect::to('/platform/ios/report/btrc')->with(compact('iosDayWise', 'btrcFileName'));

        //return Redirect::to('ios/reports/iosforbtrc')->with('iosdaywises', $iosDayWise);
    }



    public function dataAttachedInMailBody($date = null)
    {
        //Input Date formatting
        //$date = '20231209';
        $fromDate = Carbon::parse($date)->subDays(9)->format('Ymd'); //Getting previous days upto 9
        $toDate = Carbon::parse($date)->format('Ymd'); //Input date

        //DB MSSQL Query Execution
        $query = DB::connection('sqlsrv2')->table('CallSummary as cm')
            ->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as trafficDate"), DB::raw("(SUM(cm.CallDuration)/60) as duration") )
            ->whereBetween('cm.TrafficDate', array($fromDate, $toDate))
            ->where('cm.ReportTrafficDirection','=',1)
            ->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
            ->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),'ASC')
            ->get()
            ->toArray();

        $tblHeadingPart = "<tr style='font-weight: bold;'>";
        $tblHeadingPart .= "<td style='width: 150px;'>IOS Name</td>";
        foreach($query as $heading) {
            $tblHeadingPart .= "<td>" . Carbon::parse($heading->trafficDate)->format('d-M') ."</td>";
        }
        $tblHeadingPart .= "</tr>";


        $tblContentPart = "<tr>";
        $tblContentPart .= "<td style='font-weight: bold'>Bangla Trac (IOS)</td>";
        foreach($query as $data) {
            $tblContentPart .= "<td>" . number_format($data->duration) ."</td>";
        }
        $tblContentPart .= "</tr>";

        $tblContent = $tblHeadingPart . $tblContentPart;
        //dd($tblContent);

        return [
            'tblContent' => $tblContent
        ];
        //return view('emails.ios-btrc-report', compact('iosDayWise'));
    }

}
