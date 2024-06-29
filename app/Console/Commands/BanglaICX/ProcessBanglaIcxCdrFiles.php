<?php

namespace App\Console\Commands\BanglaICX;

use App\Traits\BanglaICXCdrFileProcessorTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBanglaIcxCdrFiles extends Command
{
    use BanglaICXCdrFileProcessorTrait;

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

    protected $running = true;
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
        while($this->running) {

            //$this->info('Starting to process cdr files...');
            //$start =  microtime(true);
            $this->process();
            //$this->info('File processing completed.');
            //echo 'Total process (/session) time: ' . (microtime(true) - $start) ."\n";
            sleep(5);
        }
        return 0;
    }

    protected function process(): int
    {

        // Get all files in the source directory and filter for .txt files
        $sourceFiles = Storage::disk('F')->files('Cataleya/raw_files');

        $files = array_filter($sourceFiles, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'txt';
        });

        // Get the full path of the destination directory
        $destinationDir = Storage::disk('F')->path('Cataleya/processed_files');

        // Get the output instance
        $output = $this->output;

        foreach ($files as $file) {
            $filePath = Storage::disk('F')->path($file);

            $check = $this->processCdrFileInfo($filePath);

            // Ignore duplicate
            if($check) {
                Log::channel('banglaicx')->info('Processing of : '. basename($filePath) . ' will begin at ' . now());

                $output->writeln('<fg=magenta>Processing of : ' . basename($filePath) . ' will begin at ' . now() . '</>');
                $this->processCdrRecord($filePath, $destinationDir);
                $output->writeln('The processing of file ' . basename($filePath) . ' has been completed at ' . now());

                Log::channel('banglaicx')->info('The processing of file ' . basename($filePath) . ' has been completed at ' . now());
            } else {
                Log::channel('banglaicx')->info( 'Duplicate file: ' . basename($filePath));
                $output->writeln('<fg=red>Duplicate file : ' . basename($filePath) . ' issue resolving ' . now() . '</>');
                $this->deleteNoneUniqueFileRecords(basename($filePath, '.txt'));
                $output->writeln('<fg=green>The file ' . basename($filePath) . ' has been passed to the re-processing state.' . now() . '</>');
            }
        }

        return 0;
    }
}
