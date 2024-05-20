<?php

namespace App\Console\Commands\InternationalGatewayCommands;

use App\Jobs\InternationalGatewayJobs\GenerateIGWDayWiseProfitLossReportJob;
use App\Jobs\InternationalGatewayJobs\MailPreparedForIGWDayWiseProfitLossReportJob;
use Illuminate\Console\Command;

class IGWDayWiseProfitLossReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igw:day-wise-profit-loss-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Day wise profit loss report';

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
//        GenerateIGWDayWiseProfitLossReportJob::dispatch()->chain([
//            function () {
//                MailPreparedForIGWDayWiseProfitLossReportJob::dispatch();
//            }
//        ]);
        MailPreparedForIGWDayWiseProfitLossReportJob::dispatch();
    }
}
