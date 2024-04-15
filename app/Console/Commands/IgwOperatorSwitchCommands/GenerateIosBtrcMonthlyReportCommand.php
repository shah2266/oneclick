<?php

namespace App\Console\Commands\IgwOperatorSwitchCommands;

use App\Jobs\IgwOperatorSwitchJobs\IosBtrcMonthlyReportJob;
use App\Jobs\IgwOperatorSwitchJobs\MailPreparedForIosBtrcMonthlyReportJob;
use Illuminate\Console\Command;

class GenerateIosBtrcMonthlyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ios:btrc-monthly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate BTRC monthly report from IOS platform.';

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
        IosBtrcMonthlyReportJob::dispatch()->chain([
            function () {
                MailPreparedForIosBtrcMonthlyReportJob::dispatch();
            }
        ]);
    }
}
