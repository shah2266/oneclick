<?php

namespace App\Console\Commands\IgwAndIosCommands;

use App\Jobs\IgwAndIosJobs\IosAndIgwClientsReportFromIgwAndIosJob;
use App\Jobs\IgwAndIosJobs\MailPreparedForIosAndIgwClientsReportJob;
use Illuminate\Console\Command;

class IosAndIgwClientsReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'both:ios-and-igw-clients-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ios and igw wise clients report from IGW and IOS';

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
        IosAndIgwClientsReportFromIgwAndIosJob::dispatch()->chain([
            function() {
                MailPreparedForIosAndIgwClientsReportJob::dispatch();
            }
        ]);
    }
}
