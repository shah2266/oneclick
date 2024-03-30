<?php

namespace App\Jobs\IgwAndIosJobs;

use App\Http\Controllers\IGWANDIOS\IofInOutBoundReportController;
use App\Jobs\CleanUpDirectory;
use App\Mail\IgwAndIosMails\SendIofInOutDayWiseReport;
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

class MailPreparedForIofInOutDayWiseJob implements ShouldQueue
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
        $template = $this->findMailTemplate('both:iof-in-out-report');
        $files = glob(public_path() . DIRECTORY_SEPARATOR . 'platform\igwandios\iof\schedule\inoutbound' . DIRECTORY_SEPARATOR . '*.xlsx');
        $date = Carbon::yesterday()->format('Ymd');
        $report = new IofInOutBoundReportController();
        $data = $report->dataAttachedInMailBody($date);

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        // Send the email
        Mail::send(new SendIofInOutDayWiseReport($data, $foundFiles, $template));

        if (count(Mail::failures()) === 0) {
            Log::channel('noclick')->info('Iof in-out day wise report sent successfully at: ' . now());
            // Additional logic if email sent successfully
            // Dispatch the CleanUpDirectory job after sending the email
            CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
            Log::channel('noclick')->info('Directory clean at: ' . now());
        } else {
            Log::channel('noclick')->error('Failed to send Iof in-out day wise report email!!!');
            // Additional logic if email sending fails
        }
    }
}
