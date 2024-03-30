<?php

namespace App\Console\Commands\InternationalGatewayCommands;

use App\Jobs\InternationalGatewayJobs\IgwCallSummaryReportJob;
use App\Jobs\InternationalGatewayJobs\MailPreparedForIgwCallSummaryReportJob;
use App\Jobs\InternationalGatewayJobs\MailPreparedForIgwOutgoingReportJob;
use Illuminate\Console\Command;

class IgwCallSummaryReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igw:call-summary-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Igw call summary report.';

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
        IgwCallSummaryReportJob::dispatch()->chain([
            function () {
                MailPreparedForIgwCallSummaryReportJob::dispatch();
                MailPreparedForIgwOutgoingReportJob::dispatch()->delay(now()->addMinutes());
            }
        ]);
    }
}
