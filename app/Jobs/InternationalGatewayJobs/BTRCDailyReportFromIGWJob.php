<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Http\Controllers\IGW\BtrcReportController;
use App\Traits\ReportDateHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class BTRCDailyReportFromIGWJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ReportDateHelper;

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
        Log::channel('noclick')->info('Generate BTRC daily report at: ' . now());
        //$toDate = $fromDate = '20231101';
        //File save temp directory
        $directory  = '/platform/igw/schedule/btrc/';

        list($fromDate, $toDate) = $this->setReportDateWithSubDays();

        $report     = new BtrcReportController();
        $report->dayWiseReport($fromDate, $toDate, $directory, true);
        $report->iosAndAnsWiseReport($toDate, $toDate, $directory, true);
    }
}
