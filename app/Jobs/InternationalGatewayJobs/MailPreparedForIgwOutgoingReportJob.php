<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Jobs\CleanUpDirectory;
use App\Mail\DefaultSendMailTemplate;
use App\Mail\SendMailTemplate;
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

class MailPreparedForIgwOutgoingReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesMailTemplate;

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
        // Commercial email addresses
//        $toAddresses = ['alimul.razi@btraccl.com'];
//        $ccAddresses = [
//            'btraccore@btraccl.com',
//            'noc@btraccl.com',
//            'billing.team@btraccl.com'
//        ];

        $template = $this->findMailTemplate('igw:call-summary-og-report');

        // File directory
        $directory = public_path(). DIRECTORY_SEPARATOR .'platform\igw\schedule\callsummary'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . Carbon::yesterday()->format('d-M-Y').' Outgoing Call Status.xlsx');

        // Get all directory files
        $AllFiles = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Log::info("Directory: {$this->directory}");
        //Log::info("Files: " . implode(', ', $files));

        if (!empty($files)) {
            Log::channel('noclick')->info("IGW day wise outgoing report.");
            //echo "[". now() . "] Processing: Reporting sending started..." . PHP_EOL;

            Mail::send(new DefaultSendMailTemplate($files, $template));

            Log::channel('noclick')->info("Report send successfully!");

            // Clean up: Delete or move files after the email is sent successfully
            // Dispatch a cleanup job after a delay
            CleanUpDirectory::dispatch($AllFiles)->delay(now()->addMinutes());
        } else {
            Log::channel('noclick')->error("No files found in directory: $directory");
        }
    }
}
