<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Http\Controllers\IGW\IGWDestinationWiseProfitLossReportController;
use App\Traits\ReportDateHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class GenerateIGWDayWiseProfitLossReportJob implements ShouldQueue
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
        list($fromDate, $toDate) = $this->getDatesForCurrentMonth();

        $report = new IGWDestinationWiseProfitLossReportController();
        $report->generateExcel($fromDate, $toDate, true);
        Log::channel('noclick')->info('Generated destination wise profit loss report from igw platform report at:' . now());
    }
}
