<?php

namespace App\Console\Commands\InternationalGatewayCommands;

use App\Jobs\InternationalGatewayJobs\MailPreparedForOSWiseReportJob;
use App\Jobs\InternationalGatewayJobs\OSWiseReportJob;
use Illuminate\Console\Command;

class OSWiseReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igw:os-wise-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'OS wise report';

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
        OSWiseReportJob::dispatch()->chain([
            function () {
                MailPreparedForOSWiseReportJob::dispatch();
            }
        ]);
    }
}
