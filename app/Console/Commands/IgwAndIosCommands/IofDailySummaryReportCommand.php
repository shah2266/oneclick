<?php

namespace App\Console\Commands\IgwAndIosCommands;

use App\Jobs\IgwAndIosJobs\IofDailySummaryReportJob;
use App\Jobs\IgwAndIosJobs\MailPreparedForIofDailySummaryReportJob;
use Illuminate\Console\Command;

class IofDailySummaryReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'both:iof-daily-summary-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IOF daily summary report generate from IGW and IOS platform.';

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
     * @return int
     */
    public function handle(): int
    {
        // Dispatch the IosDailyReportJob to generate the report
        IofDailySummaryReportJob::dispatch()->chain([
            function() {
                MailPreparedForIofDailySummaryReportJob::dispatch();
            }
        ]);

        return 0;
    }
}
