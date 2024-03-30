<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Http\Controllers\IGW\CallSummaryController;
use App\Mail\InternationalGatewayMails\SendIgwCallSummaryReport;
use App\Traits\FileFinderTrait;
use App\Traits\HandlesMailTemplate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailPreparedForIgwCallSummaryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FileFinderTrait, HandlesMailTemplate;

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
     */
    public function handle()
    {

        $template = $this->findMailTemplate('igw:call-summary-report');

        // File directory
        $files = glob(public_path() . DIRECTORY_SEPARATOR . 'platform\igw\schedule\callsummary' . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        $date = Carbon::yesterday()->format('Ymd');
        $report = new CallSummaryController();
        $data = $report->dataAttachedInMailBody($date, $date);

        //Log::info("Directory: {$this->directory}");
        //Log::info("Files: " . implode(', ', $files));

        if (!empty($foundFiles)) {
            Log::channel('noclick')->info("IGW call summary report.");
            //echo "[". now() . "] Processing: Reporting sending started..." . PHP_EOL;

            Mail::send(new SendIgwCallSummaryReport($data, $foundFiles, $template));

            Log::channel('noclick')->info("Report send successfully!");

            // Clean up: Delete or move files after the email is sent successfully
            // Dispatch a cleanup job after a delay
            //CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
        } else {
            Log::channel('noclick')->error("No files found in directory");
        }
    }
}
