<?php

namespace App\Console\Commands\IgwAndIosCommands;

use App\Jobs\IgwAndIosJobs\IofInOutDayWiseJob;
use App\Jobs\IgwAndIosJobs\MailPreparedForIofInOutDayWiseJob;
use Illuminate\Console\Command;

class IofInOutDayWiseReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'both:iof-in-out-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate IOF in-out day wise report from IGW and IOS platform.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        IofInOutDayWiseJob::dispatch()->chain([
            function() {
                MailPreparedForIofInOutDayWiseJob::dispatch();
            }
        ]);
    }
}
