<?php

namespace App\Http\Controllers;

use App\Models\NoclickSchedule;
use App\Traits\CdrFileStatus;
use App\Traits\ScheduleProcessing;
use Carbon\Carbon;

class TestController
{

    use CdrFileStatus, ScheduleProcessing;


    public function testing()
    {
        //$this->processSchedules('Null');

        $sequences = $this->missingFileSequences();
        $data = $this->fileMissingNotifications($sequences);
        echo $data;
    }

}
