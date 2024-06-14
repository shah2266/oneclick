<?php

namespace App\Http\Controllers;

use App\Models\NoclickSchedule;
use App\Traits\CdrFileStatus;
use App\Traits\ScheduleProcessing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestController
{

    use CdrFileStatus, ScheduleProcessing;


    public function index()
    {
        //$this->processSchedules('Null');

        // Get the current date
        $currentDate = Carbon::today();

        // Initial date range for today
        $fromDate = $currentDate->copy()->subDay()->startOfDay();
        $toDate = $currentDate->copy()->subDay()->endOfDay();

        $query = DB::connection('mysql8')->select(
            "select file_sequence_no, file_name from cdr_files where created_at between '2024-06-02 00:00:00' and '2024-06-10 23:59:59'"
        );

        $sequenceNumbers = array_column($query, 'file_sequence_no');

        //$this->cdrFilesQuery('mysql8', $fromDate, $toDate, 1);

        $sequenceNumbers = [60,87,88,2,3,5,6,10];
        $sequence_reset_point = 1;
        $reset_index = array_search($sequence_reset_point, $sequenceNumbers);

        $before_rest_sequences = array_slice($sequenceNumbers, 0, $reset_index);
        $after_reset_sequences = array_slice($sequenceNumbers, $reset_index);

        dump($after_reset_sequences);
        dump($after_reset_sequences);
        //$test = array_merge($this->findMissingSequences($before_rest_sequences), $this->findMissingSequences($after_reset_sequences));

        //dump($test);
        //$data = $this->fileMissingNotifications($sequences);
        //echo $data;
        dd('End');
    }

    public function findMissingSequences($sequences): array
    {
        $first_value = min($sequences);
        $last_value = max($sequences);

        $missingSequences = [];

        for ($i = $first_value; $i <= $last_value; $i++) {
            if(!in_array($i, $sequences)) {
                $missingSequences[] = $i;
            }
        }

        return $missingSequences;

    }

}
