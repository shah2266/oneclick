<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Http\Controllers\IGW\OSReportController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class OSWiseReportJob implements ShouldQueue
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
        Log::channel('noclick')->info('Generate os wise report: ' . now());

        //File save temp directory
        $directory = '/platform/igw/schedule/oswise/';

        $report = new OSReportController();
        $inputFromDate = Carbon::yesterday()->format('Ymd'); // Set the input from yesterday
        $inputToDate = Carbon::yesterday()->format('Ymd'); // Set the input to yesterday
        $report->incoming($inputFromDate, $inputToDate, $directory, true);
        $report->outgoing($inputFromDate, $inputToDate, $directory,true);
    }
}
