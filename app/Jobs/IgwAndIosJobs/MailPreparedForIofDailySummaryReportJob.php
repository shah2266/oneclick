<?php

namespace App\Jobs\IgwAndIosJobs;

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

class MailPreparedForIofDailySummaryReportJob implements ShouldQueue
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

        //Commercial email addresses
//        $toAddresses = ['report@iofbd.com'];
//        $ccAddresses = [
//            'noc@iofbd.com',
//            'iofnoc2015@gmail.com',
//            'zainal.abedin@btraccl.com',
//            'rokib.mahmud@btraccl.com',
//            'jahangir.alam@btraccl.com',
//            'tanzila.mahzabin@bd.gt.com',
//            'noc@btraccl.com',
//            'billing@iofbd.com',
//            'dipendu.saha@iofbd.com',
//            'billing.team@btraccl.com'
//        ];


        $template = $this->findMailTemplate('both:iof-daily-summary-report');

        // File directory
        $directory = public_path() . DIRECTORY_SEPARATOR . 'platform\igwandios\iof\schedule\callsummary'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files, 'd.m.Y');

        if(!empty($foundFiles)){

            // Create an instance of the Mailable
            $email = new DefaultSendMailTemplate($foundFiles, $template);

            // Send the email
            Mail::send($email);

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
