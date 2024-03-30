<?php

namespace App\Console\Commands\IgwAndIosCommands;

use App\Jobs\IgwAndIosJobs\ComparisonReportJob;
use App\Jobs\IgwAndIosJobs\MailPreparedForComparisonReportJob;
use Illuminate\Console\Command;

class ComparisonReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'both:comparison-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate IGW and IOS day wise comparison report.';

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
        ComparisonReportJob::dispatch()->chain([
            function() {
                MailPreparedForComparisonReportJob::dispatch();
            }
        ]);
    }
}
