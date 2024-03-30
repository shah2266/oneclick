<?php

namespace App\Console\Commands\IgwOperatorSwitchCommands;

use App\Jobs\IgwOperatorSwitchJobs\IosDailyReportJob;
use App\Jobs\IgwOperatorSwitchJobs\MailPreparedForIosDailyReportJob;
use Illuminate\Console\Command;

class IOSDailyCallSummaryReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ios:daily-call-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IOS daily call summary report from the IOS platform.';

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
        IosDailyReportJob::dispatch()->chain([
            // Dispatch the SendIOSDailyCallSummaryReport after the IosDailyReportJob is completed
            function () {
                MailPreparedForIosDailyReportJob::dispatch();
            }
        ]);

        return 0;
    }
}
