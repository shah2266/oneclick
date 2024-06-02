<?php

namespace App\Console\Commands\BanglaICX;

use App\Http\Controllers\ICX\ProcessedBanglaIcxCdrFilesController;
use Illuminate\Console\Command;

class ProcessBanglaIcxCdrFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'icx:process-cdr-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Bangla ICX raw CDR files and insert into database';

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
     * @return int
     */
    public function handle(): int
    {
        $controller = new ProcessedBanglaIcxCdrFilesController();
        $this->info('Starting to process cdr files...');
        $controller->process();
        //$response = $controller->process();
        //$this->info($response->getData()->message);
        $this->info('File processing completed.');

        return 0;
    }
}
