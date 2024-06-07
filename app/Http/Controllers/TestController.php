<?php

namespace App\Http\Controllers;

use App\Models\NoclickSchedule;
use App\Traits\CdrFileStatus;
use App\Traits\ScheduleProcessing;
use Carbon\Carbon;

class TestController
{

    use CdrFileStatus, ScheduleProcessing;


    public function index()
    {
        //$this->processSchedules('Null');

        // Get the current date
//        $currentDate = Carbon::today();
//
//        // Initial date range for today
//        $fromDate = $currentDate->copy()->startOfDay();
//        $toDate = $currentDate->copy()->endOfDay();
//
//        //$platforms = $this->platforms();
//
//        $con = ['sqlsrv1', 'sqlsrv2'];
//        $conKey = 0;
//        $missingFiles = [];
//        foreach ($this->platforms() as $key => $switches) {
//
//            $platformName = $key;
//
//            foreach($switches as $switchId => $switchName) {
//                $result = $this->cdrFilesQuery($con[$conKey], $fromDate, $toDate, $switchId);
//                $sequenceNumbers = array_column($result, 'CDRFileSequenceNo');
//                $missingSequence = $this->findMissingSequence($sequenceNumbers);
//
//                // If missing sequence is found, store the result
//                if (!empty($missingSequence)) {
//                    $missingFiles[$platformName . ',' . $switchName .','. $fromDate .','. $toDate] = $missingSequence;
//                }
//            }
//
//            $conKey++;
//        }
//
//       dump($this->missingFileSequences());
        //dump($missingFiles);


        //$data = $this->fileMissingNotifications($sequences);
        //echo $data;
        dd('End');
    }

}
