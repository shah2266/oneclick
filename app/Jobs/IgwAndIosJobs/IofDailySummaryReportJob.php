<?php

namespace App\Jobs\IgwAndIosJobs;

use App\Http\Controllers\IGWANDIOS\IofDailySummaryReportController;
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

class IofDailySummaryReportJob implements ShouldQueue
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
        list($fromDate, $toDate) = $this->setReportDateRange();
        $report = new IofDailySummaryReportController();
        $report->generateReport($toDate, true);
        Log::channel('noclick')->info('Generated ios daily report at:' . now());
    }
}
