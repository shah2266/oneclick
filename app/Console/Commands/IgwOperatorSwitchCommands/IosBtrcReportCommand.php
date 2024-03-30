<?php

namespace App\Console\Commands\IgwOperatorSwitchCommands;

use App\Jobs\IgwOperatorSwitchJobs\MailPreparedForIosBtrcReportJob;
use Illuminate\Console\Command;

class IosBtrcReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ios:ios-btrc-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IOS day wise BTRC report.';

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
        MailPreparedForIosBtrcReportJob::dispatch();
    }
}
