<?php

namespace App\Http\Controllers\IGWANDIOS;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class IofReportController extends Controller
{
    //All generated files show in view
    public function index() {
        //Local C directory
        $localDirectory = Storage::disk('partitionC')->files('Reports');

        //Laravel public directory
        $publicDirectory = Storage::disk('public')->files('Reports');

        $files = array_unique(array_merge($localDirectory, $publicDirectory));

        return view('platform.igwandios.iof.OldDailyReport.index', compact('files'));
    }

    //Download IOF Daily Report
    public function getFile($filename): BinaryFileResponse
    {

        if(!Storage::disk('public')->exists('/Reports/' . $filename)) {
            //$file = 'C:/Users/User-PC/Desktop/Btrac_IOF_ReportingApps_14May17/Reports/'.$filename; //This pc directory
            $file = 'C:/Users/shah.alam/Desktop/Btrac_IOF_Reporting_AppsProject_20190310/Reports/'.$filename; //Shah Alam PC directory
        } else {
            $file = public_path(). '/Reports/'.$filename; //Shah Alam PC directory
        }

        $headers = [
            'Content-Type' => 'application/ms-excel',
        ];

        return response()->download($file);
    }

    //Zip Download IOF daily report
    public function zipCreator() {

        //Local C directory
        $localDirectory = Storage::disk('partitionC')->files('Reports');

        //Laravel public directory
        $publicDirectory = Storage::disk('public')->files('Reports');


        $date = 'IOF daily report '. Carbon::now()->subdays()->format('d-M-Y');

        if(count($localDirectory) >= count($publicDirectory)) {
            $zip_file = 'C:/Users/shah.alam/Desktop/Btrac_IOF_Reporting_AppsProject_20190310/Zipfiles/'.$date.'.zip'; //Store all created zip files here
            $path = 'C:/Users/shah.alam/Desktop/Btrac_IOF_Reporting_AppsProject_20190310/Reports/';
        } else {
            $zip_file =  public_path(). '/Reports/ZipFiles/'.$date.'.zip'; //Store all created zip files here
            $path = public_path(). '/Reports/';
        }

        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        //$path = public_path(). '/Platform/igwandios/comparison/main/';

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $flag = 0;
        foreach ($files as $file) {
            // We're skipping all subfolders

            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                // extracting filename with substr/strlen
                $relativePath = $date.'/' . substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
                $flag = 1;
            }

        }

        if($flag == 0) {
            return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('danger','Directory is empty. Please generate reports');
        } else {
            $zip->close();
            return response()->download($zip_file);
        }
    }

    //Permanently delete file
    public function deleteFile($filename): RedirectResponse
    {
        Storage::disk('partitionC')->delete('/Reports/'.$filename);
        Storage::disk('public')->delete('/Reports/'.$filename);
        return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('message','Report successfully deleted');
    }


    //Clear Directory
    public function cleanDir(): RedirectResponse
    {
        $clean1 = Storage::disk('partitionC')->delete(Storage::disk('partitionC')->files('Reports'));
        $clean2 = Storage::disk('public')->delete(Storage::disk('public')->files('Reports'));
        if($clean1 || $clean2) {
            return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('success','Report directory clean!');
        } else {
            return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('danger','There are a problem to delete files');
        }
    }

    public function executeReport(): RedirectResponse
    {
        //$argument = '2020-11-19';

        //exec("START C:\Users\shah.alam\Desktop\Btrac_IOF_Reporting_AppsProject_20190310\Btrac_IOF_ReportingApps.exe {$argument}");
        //return Redirect::to('platform/igwandios/report/iof/callsummary/old');

         if(Carbon::now()->format('Hi') <= '1815') {
             //exec("START C:\Users\shah.alam\Desktop\Btrac_IOF_Reporting_AppsProject_20190310\Btrac_IOF_ReportingApps.exe");
             exec("START C:\Users\shah.alam\Desktop\Btrac_IOF_Reporting_AppsProject_20190310\Btrac_IOF_ReportingApps.exe");
             return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('success','All reports successfully generated');
         } else {
             return Redirect::to('platform/igwandios/report/iof/callsummary/old')->with('danger','Time over! Please run it manually.');
         }
    }

}
