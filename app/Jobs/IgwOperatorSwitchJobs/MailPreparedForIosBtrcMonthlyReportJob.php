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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\IOS\IosBtrcMonthlyReportController;

class MailPreparedForIosBtrcMonthlyReportJob implements ShouldQueue
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
        $companyNames = new IosBtrcMonthlyReportController();

        foreach ($companyNames->companies() as $companyName) {
            try {
                $template = $this->findMailTemplateByName($companyName);

                // File directory
                $directory = public_path() . DIRECTORY_SEPARATOR . 'platform\ios\schedule\btrcmonthlyreport\icxandanswise'; // Adjust the path as needed

                // Get the previous month name and year in the "Month-Year" format
                $previousMonth = Carbon::now()->subMonth()->format('F-Y');

                $template['subject'] = $template['subject'] . ' - (' . $previousMonth . ')';

                // Ensure the correct directory separator is used
                $files = glob($directory . DIRECTORY_SEPARATOR . $companyName . ', ' . $previousMonth . '.xlsx');

                if (!empty($files)) {

                    $email = new DefaultSendMailTemplate($files, $template);
                    Mail::send($email);

                    Log::channel('noclick')->info("Report send successfully!");

                    // Clean up: Delete or move files after the email is sent successfully
                    // Dispatch a cleanup job after a delay
                    CleanUpDirectory::dispatch($files)->delay(now()->addMinutes());
                } else {
                    Log::channel('noclick')->error("No files found in directory for: $companyName");
                }
            } catch (ModelNotFoundException $e) {
                Log::channel('noclick')->error("Mail template not found for company: $companyName");
            }
        }
    }

}
