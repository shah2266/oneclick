<?php

namespace App\Console\Commands;

use App\Jobs\CdrStatus\MailPreparedForCdrStatusJob;
use Illuminate\Console\Command;

class CDRFileStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'both:cdr-file-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        MailPreparedForCdrStatusJob::dispatch();
    }
}
