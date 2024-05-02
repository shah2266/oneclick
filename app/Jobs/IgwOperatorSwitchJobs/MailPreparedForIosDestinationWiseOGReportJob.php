<?php

namespace App\Jobs\IgwOperatorSwitchJobs;

use App\Jobs\CleanUpDirectory;
use App\Mail\DefaultSendMailTemplate;
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

class MailPreparedForIosDestinationWiseOGReportJob implements ShouldQueue
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
        $template = $this->findMailTemplate('ios:destination-wise-outgoing-report');
        list($fromDate, $toDate) = $this->setReportDateRange();
        $fromDate = Carbon::parse($fromDate)->format('d-M-Y'); // Set the input from yesterday
        $toDate = Carbon::parse($toDate)->format('d-M-Y'); // Set the input to yesterday

        // File directory
        $directory = public_path() . DIRECTORY_SEPARATOR . 'platform\ios\schedule\destinationwisereport'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . 'ios_des_report '.$fromDate.' to '.$toDate.'.xlsx');

        //Only process yesterday files
        //$foundFiles = $this->findFilesByNeedle($files);

        if(!empty($files)){

            // Send the email
            Mail::send(new DefaultSendMailTemplate($files, $template));

            if(count(Mail::failures()) === 0) {

                // Dispatch the CleanUpDirectory job after sending the email
                CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
            } else {
                Log::channel('noclick')->error('Failed to send IOS daily report email!!!');
            }

        } else {
            Log::channel('noclick')->error("No files found in directory: $directory");
        }
    }
}
