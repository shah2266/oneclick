<?php

namespace App\Jobs\IgwOperatorSwitchJobs;

use App\Http\Controllers\IOS\IosDestinationWiseOutgoingReportController;
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

class GenerateIosDestinationWiseOGReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ReportDateHelper;

    protected $timeout = 600;

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
        set_time_limit($this->timeout);

        list($fromDate, $toDate) = $this->setReportDateRange();

        $report = new IosDestinationWiseOutgoingReportController();
        $report->generateExcel($fromDate, $toDate, true);
        Log::channel('noclick')->info('Generated destination wise report from ios platform report at:' . now());
    }
}
