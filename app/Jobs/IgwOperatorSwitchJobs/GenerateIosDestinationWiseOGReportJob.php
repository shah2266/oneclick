<?php

namespace App\Jobs\IgwOperatorSwitchJobs;

use App\Http\Controllers\IOS\IosDestinationWiseOutgoingReportController;
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
        $firstDateOfMonth = Carbon::now()->firstOfMonth()->format('Ymd');
        $currentDate = Carbon::now()->subDays()->format('Ymd');

        $report = new IosDestinationWiseOutgoingReportController();
        $report->generateExcel($firstDateOfMonth, $currentDate, true);
        Log::channel('noclick')->info('Generated destination wise report from ios platform report at:' . now());
    }
}
