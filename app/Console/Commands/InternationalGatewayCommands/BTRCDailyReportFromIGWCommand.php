<?php

namespace App\Console\Commands\InternationalGatewayCommands;

use App\Jobs\InternationalGatewayJobs\BTRCDailyReportFromIGWJob;
use App\Jobs\InternationalGatewayJobs\MailPreparedForBTRCDailyReportFromIGWJob;
use Illuminate\Console\Command;

class BTRCDailyReportFromIGWCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igw:btrc-daily-report-from-igw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BTRC daily report from igw platform';

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
        BTRCDailyReportFromIGWJob::dispatch()->chain([
            function () {
                MailPreparedForBTRCDailyReportFromIGWJob::dispatch();
            }
        ]);
    }
}
