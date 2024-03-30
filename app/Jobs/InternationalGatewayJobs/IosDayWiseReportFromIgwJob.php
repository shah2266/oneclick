<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Http\Controllers\IGW\IOSReportController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class IosDayWiseReportFromIgwJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle()
    {
        Log::channel('noclick')->info('Generate ios and igw wise clients report from IGW and IOS: ' . now());

        //File save temp directory
        $directory = '/platform/igw/schedule/ioswise/';

        $iosClientsReport = new IOSReportController();
        $inputFromDate = Carbon::yesterday()->format('Ymd'); // Set the input from yesterday
        $inputToDate = Carbon::yesterday()->format('Ymd'); // Set the input to yesterday
        $iosClientsReport->incoming($inputFromDate, $inputToDate, $directory, true);
        $iosClientsReport->outgoing($inputFromDate, $inputToDate, $directory,true);
    }
}
