<?php

namespace App\Jobs\IgwOperatorSwitchJobs;

use App\Http\Controllers\IOS\BtrcController;
use App\Mail\IgwOperatorSwitchMails\SendIosBtrcReport;
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

class MailPreparedForIosBtrcReportJob implements ShouldQueue
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
        $template = $this->findMailTemplate('ios:ios-btrc-report');

        $date = Carbon::yesterday()->format('Ymd');
        $report = new BtrcController();
        $data = $report->dataAttachedInMailBody($date);

        // Send the email
        Mail::send(new SendIosBtrcReport($data, $template));

        if (count(Mail::failures()) === 0) {
            Log::channel('noclick')->info('IOS day wise report for BTRC sent successfully at: ' . now());
            // Additional logic if email sent successfully

            Log::channel('noclick')->info('Directory clean at: ' . now());
        } else {
            Log::channel('noclick')->error('Failed to send ios day wise report for BTRC!!!');
            // Additional logic if email sending fails
        }
    }
}
