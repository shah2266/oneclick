<?php

namespace App\Jobs\InternationalGatewayJobs;

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

class MailPreparedForIosDayWiseReportFromIgwJob implements ShouldQueue
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

        // Commercial email addresses
//        $toAddresses = ['masum.hasan@btraccl.com'];
//        $ccAddresses = [
//            'btraccore@btraccl.com',
//            'fahad.islam@btraccl.com',
//            'arif.hossain@btraccl.com',
//            'noc@btraccl.com',
//            'billing.team@btraccl.com'
//        ];


        $template = $this->findMailTemplate('igw:ios-wise-report');


        // File directory
        $directory = public_path(). DIRECTORY_SEPARATOR .'platform\igw\schedule\ioswise'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        //Log::info("Directory: {$this->directory}");
        //Log::info("Files: " . implode(', ', $files));

        if (!empty($foundFiles)) {
            Log::channel('noclick')->info("Ios wise in out report from igw platform.");
            //echo "[". now() . "] Processing: Reporting sending started..." . PHP_EOL;

            $email = new DefaultSendMailTemplate($foundFiles, $template);
            Mail::send($email);

            Log::channel('noclick')->info("Report send successfully!");

            // Clean up: Delete or move files after the email is sent successfully
            // Dispatch a cleanup job after a delay
            CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
        } else {
            Log::channel('noclick')->error("No files found in directory: $directory");
        }
    }
}
