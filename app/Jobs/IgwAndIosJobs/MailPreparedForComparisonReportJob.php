<?php

namespace App\Jobs\IgwAndIosJobs;

use App\Http\Controllers\IGWANDIOS\ComparisonReportController;
use App\Jobs\CleanUpDirectory;
use App\Mail\IgwAndIosMails\SendComparisonReport;
use App\Models\NoclickSchedule;
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

class MailPreparedForComparisonReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FileFinderTrait, HandlesMailTemplate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $template = $this->findMailTemplate('both:comparison-report');

        $files = glob(public_path() . DIRECTORY_SEPARATOR . 'platform\igwandios\schedule\comparison' . DIRECTORY_SEPARATOR . '*.xlsx');
        $date = Carbon::yesterday()->format('Ymd');
        $report = new ComparisonReportController();
        $data = $report->dataAttachedInMailBody($date, $date);

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        // Send the email
        Mail::send(new SendComparisonReport($data, $foundFiles, $template));

        if (count(Mail::failures()) === 0) {
            Log::channel('noclick')->info('Comparison report email sent successfully at: ' . now());
            // Additional logic if email sent successfully
            // Dispatch the CleanUpDirectory job after sending the email
            CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
            Log::channel('noclick')->info('Directory clean at: ' . now());
        } else {
            Log::channel('noclick')->error('Failed to send comparison report email!!!');
            // Additional logic if email sending fails
        }
    }
}
