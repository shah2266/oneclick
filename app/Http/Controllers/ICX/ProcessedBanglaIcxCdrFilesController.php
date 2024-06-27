<?php

namespace App\Http\Controllers\ICX;

use App\Http\Controllers\Controller;
use App\Traits\BanglaICXCdrFileProcessorTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessedBanglaIcxCdrFilesController extends Controller
{
    use BanglaICXCdrFileProcessorTrait;

    public function index()
    {
        //$test = $this->process();
        dd('End');
    }

    //private $sourceDir = 'platform\icx\CDR\raw_files';

    /**
     * @return JsonResponse
     */
//    public function process(): JsonResponse
//    {
//
//        // Get all files in the source directory and filter for .txt files
//        $sourceFiles = Storage::disk('F')->files('Cataleya/raw_files');
//
//        $files = array_filter($sourceFiles, function($file) {
//            return pathinfo($file, PATHINFO_EXTENSION) === 'txt';
//        });
//
//        // Get the full path of the destination directory
//        $destinationDir = Storage::disk('F')->path('Cataleya/processed_files');
//
//        foreach ($files as $file) {
//            $filePath = Storage::disk('F')->path($file);
//
//            $check = $this->processCdrFileInfo($filePath);
//
//            // Ignore duplicate
//            if($check) {
//                Log::channel('banglaicx')->info('Processing of : '. basename($filePath) . ' will begin at ' . now());
//                echo 'Processing of : '. basename($filePath) . ' will begin at ' . now() ."\n";
//                $this->processCdrRecord($filePath, $destinationDir);
//                echo "<span style='color: darkolivegreen'>The processing of file " . basename($filePath) . " has been completed at " . now() ."</span>\n";
//                Log::channel('banglaicx')->info('The processing of file ' . basename($filePath) . ' has been completed at ' . now());
//            } else {
//                Log::channel('banglaicx')->info( 'Duplicate file: ' . basename($filePath));
//            }
//        }
//
//        return response()->json(['message' => 'File processing completed.']);
//    }
}
