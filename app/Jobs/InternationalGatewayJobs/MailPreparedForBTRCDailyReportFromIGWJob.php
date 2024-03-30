<?php

namespace App\Jobs\InternationalGatewayJobs;

use App\Jobs\CleanUpDirectory;
use App\Mail\DefaultSendMailTemplate;
use App\Mail\SendMailTemplate;
use App\Models\NoclickMailTemplate;
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

class MailPreparedForBTRCDailyReportFromIGWJob implements ShouldQueue
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
        $template = $this->findMailTemplate('igw:btrc-daily-report-from-igw');

        // File directory
        $directory = public_path(). DIRECTORY_SEPARATOR .'platform\igw\schedule\btrc'; // Adjust the path as needed

        // Ensure the correct directory separator is used
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.xlsx');

        //Only process yesterday files
        $foundFiles = $this->findFilesByNeedle($files);

        if (!empty($foundFiles)) {
            Log::channel('noclick')->info("BTRC daily report sending ...");
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
