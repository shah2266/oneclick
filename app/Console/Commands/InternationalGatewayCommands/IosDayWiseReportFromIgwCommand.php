<?php

namespace App\Console\Commands\InternationalGatewayCommands;

use App\Jobs\InternationalGatewayJobs\IosDayWiseReportFromIgwJob;
use App\Jobs\InternationalGatewayJobs\MailPreparedForIosDayWiseReportFromIgwJob;
use Illuminate\Console\Command;

class IosDayWiseReportFromIgwCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igw:ios-wise-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ios wise report from igw platform.';

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
        IosDayWiseReportFromIgwJob::dispatch()->chain([
            function () {
                MailPreparedForIosDayWiseReportFromIgwJob::dispatch();
            }
        ]);
    }
}
