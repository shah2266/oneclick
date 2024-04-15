<?php

namespace App\Jobs\IgwOperatorSwitchJobs;

use App\Jobs\CleanUpDirectory;
use App\Mail\DefaultSendMailTemplate;
use App\Mail\SendMailTemplate;
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

class MailPreparedForIosDailyReportJob implements ShouldQueue
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
        $template = $this->findMailTemplate('ios:daily-call-summary');

        // File directory
        $directory = public_path() . DIRECTORY_SEPARATOR . 'platform\ios\callsummary'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        if(!empty($foundFiles)){

            // Send the email
            Mail::send(new DefaultSendMailTemplate($foundFiles, $template));

            if(count(Mail::failures()) === 0) {
                Log::channel('noclick')->info('IOS Daily call summary report send successfully at: ' . now());

                // Dispatch the CleanUpDirectory job after sending the email
                CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
                Log::channel('noclick')->info('Directory clean at: ' . now());

            } else {
                Log::channel('noclick')->error('Failed to send IOS daily report email!!!');
            }

        } else {
            Log::channel('noclick')->error("No files found in directory: $directory");
        }

    }
}
