<?php

namespace App\Jobs\IgwAndIosJobs;

use App\Http\Controllers\IGWANDIOS\ComparisonReportController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;

class ComparisonReportJob implements ShouldQueue
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
        $date = Carbon::yesterday()->format('Ymd');
        $report = new ComparisonReportController();
        $report->incomingReport($date, $date,true);
        $report->outgoingReport($date, $date,true);
        Log::channel('noclick')->info('Generated IGW and IOS comparison report at:' . now());
    }
}
