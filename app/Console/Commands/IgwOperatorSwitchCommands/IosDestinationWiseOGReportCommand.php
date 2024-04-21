<?php

namespace App\Console\Commands\IgwOperatorSwitchCommands;

use App\Jobs\IgwOperatorSwitchJobs\GenerateIosDestinationWiseOGReportJob;
use App\Jobs\IgwOperatorSwitchJobs\MailPreparedForIosDestinationWiseOGReportJob;
use Illuminate\Console\Command;

class IosDestinationWiseOGReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ios:destination-wise-outgoing-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate destination-wise outgoing report from IOS platform.';

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
        GenerateIosDestinationWiseOGReportJob::dispatch()->chain([
            function () {
                MailPreparedForIosDestinationWiseOGReportJob::dispatch()->delay(now()->addMinutes());
            }
        ]);
    }
}
