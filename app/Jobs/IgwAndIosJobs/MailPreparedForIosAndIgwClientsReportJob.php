<?php

namespace App\Jobs\IgwAndIosJobs;

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

class MailPreparedForIosAndIgwClientsReportJob implements ShouldQueue
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

        $template = $this->findMailTemplate('both:ios-and-igw-clients-report');

        // File directory
        $directory = public_path(). DIRECTORY_SEPARATOR .'platform\igwandios\schedule\ios_and_igw_wise'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        list($fromDate, $toDate) = $this->setReportCurrentDateRange();
        $foundFiles = $this->findFilesByNeedle($files, 'd-M-Y', $toDate);

        //Log::info("Directory: {$this->directory}");
        //Log::info("Files: " . implode(', ', $files));

        if (!empty($foundFiles)) {
            Log::channel('noclick')->info("Report sending started...!!!");
            //echo "[". now() . "] Processing: Reporting sending started..." . PHP_EOL;

            // Send the email
            Mail::send(new DefaultSendMailTemplate($foundFiles, $template));

            Log::channel('noclick')->info("Report send successfully! Directory cleaning ... ");

            // Clean up: Delete or move files after the email is sent successfully
            // Dispatch a cleanup job after a delay
            CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
        } else {
            Log::channel('noclick')->error("No files found in directory: $directory");
        }
    }

//    public function displayName() {
//        return 'Sending ios report '.now();
//    }
}
