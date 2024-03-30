<?php

namespace App\Jobs\CdrStatus;

use App\Mail\CdrStatus\SendCdrStatusReport;
use App\Traits\CdrFileStatus;
use App\Traits\HandlesMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailPreparedForCdrStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CdrFileStatus, HandlesMailTemplate;

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
        $template = $this->findMailTemplate('both:cdr-file-status');

        $sequences = $this->missingFileSequences();
        $data = $this->fileMissingNotifications($sequences);

        // Send the email
        Mail::send(new SendCdrStatusReport($data, $template));

        if (count(Mail::failures()) === 0) {
            Log::channel('noclick')->info('CDR file missing: ' . now());
        } else {
            Log::channel('noclick')->error('Failed to send ios day wise report for BTRC!!!');
            // Additional logic if email sending fails
        }
    }
}
