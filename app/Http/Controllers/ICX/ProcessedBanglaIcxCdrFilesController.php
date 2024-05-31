<?php

namespace App\Http\Controllers\ICX;

use App\Http\Controllers\Controller;
use App\Traits\BanglaICXCdrFileProcessorTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProcessedBanglaIcxCdrFilesController extends Controller
{
    use BanglaICXCdrFileProcessorTrait;

    public function index()
    {
        $test = $this->process();
        dd('End');
    }

    //private $sourceDir = 'platform\icx\CDR\raw_files';

    /**
     * @return JsonResponse
     */
    public function process(): JsonResponse
    {
        $dir_partials = public_path() . DIRECTORY_SEPARATOR . 'platform' . DIRECTORY_SEPARATOR . 'icx' . DIRECTORY_SEPARATOR . 'CDR' . DIRECTORY_SEPARATOR ;
        $sourceDir = $dir_partials . 'raw_files';
        $destinationDir = $dir_partials . 'processed_files';

        $files = glob($sourceDir . DIRECTORY_SEPARATOR . '*.txt');

        foreach ($files as $file) {
            $check = $this->processCdrFileInfo($file);
            // Ignore duplicate
            if($check) {
               $this->processCdrRecord($file, $destinationDir);
            }
        }

        return response()->json(['message' => 'File processing completed.']);
    }
}
