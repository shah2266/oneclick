<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CdrFileStatus
{

    /**
     * Find missing sequence numbers for each switch.
     *
     * @return array
     */
    public function missingFileSequences(): array
    {
        // Get the current date
        $currentDate = Carbon::today();

        // Initial date range for today
        $fromDate = $currentDate->copy()->subDay()->startOfDay();
        $toDate = $currentDate->copy()->subDay()->endOfDay();

        // Check for missing files for today
        $missingFiles = $this->retrieveMissingFilesForDateRanges($fromDate, $toDate);

        // If missing files are found, recheck by subtracting one day and adding one day to the date
        if (!empty($missingFiles)) {
            // Substract one day and check for missing files
            $fromDate->subDay();
            $missingFiles = $this->retrieveMissingFilesForDateRanges($fromDate, $toDate);
            // If still missing files are found, add one day and recheck
            if (!empty($missingFiles)) {
                $toDate->addDay();
                $missingFiles = $this->retrieveMissingFilesForDateRanges($fromDate, $toDate);
            }
        } else {
            $fromDate = $currentDate->copy()->startOfDay();
            $toDate = $currentDate->copy()->endOfDay();
            $missingFiles = $this->retrieveMissingFilesForDateRanges($fromDate, $toDate);
        }

        return $missingFiles;
    }

    protected function retrieveMissingFilesForDateRanges($fromDate, $toDate): array
    {
        $con = ['sqlsrv1', 'sqlsrv2'];
        $missingFiles = [];
        $conKey = 0;

        foreach ($this->platforms() as $key => $switches) {

            $platformName = $key;

            foreach($switches as $switchId => $switchName) {
                $result = $this->cdrFilesQuery($con[$conKey], $fromDate, $toDate, $switchId);
                $sequenceNumbers = array_column($result, 'CDRFileSequenceNo');
                $missingSequence = $this->findMissingSequence($sequenceNumbers);

                // If missing sequence is found, store the result
                if (!empty($missingSequence)) {
                    $missingFiles[$platformName . ',' . $switchName .','. $fromDate .','. $toDate] = $missingSequence;
                }
            }

            $conKey++;
        }

        return $missingFiles;
    }

    /**
     * Query CDR files from the database.
     *
     * @param string $connection
     * @param string $fromDate
     * @param string $toDate
     * @param string $switchId
     * @return array
     */
    protected function cdrFilesQuery(string $connection, string $fromDate, string $toDate, string $switchId): array
    {
        return DB::connection($connection)
            ->table('dbo.CDRFILE')
            ->select('CDRFileSequenceNo', 'FileName', 'SwitchID')
            ->whereBetween('ImportStartDateTime', [$fromDate, $toDate])
            ->where('SwitchID', $switchId)
            ->get()
            ->toArray();
    }

    /**
     * Find missing sequence numbers in a given range.
     *
     * @param array $sequenceNumbers
     * @return array
     */
    protected function findMissingSequence(array $sequenceNumbers): array
    {
        $first = min($sequenceNumbers);
        $last = max($sequenceNumbers);
        $sequences = [];

        for ($i = $first; $i < $last; $i++) {
            if (!in_array($i, $sequenceNumbers)) {
                $sequences[] = $i;
            }
        }

        return $sequences;
    }

    /**
     * Platforms name with switch names and IDs
     *
     * @return array
     */
    protected function platforms(): array
    {
        return [
            'IGW' => [
                //'1' => 'Ericsson',
                '2' => 'Dialogic',
                //'3' => 'Dialogic 2',
                '4' => 'Cataleya',
                '5' => 'Cataleya 2'
            ],
            'IOS' => [
                '1' => 'Dialogic 2',
                //'2' => 'Ericsson',
                '3' => 'Cataleya IOS',
            ]
        ];
    }


    /**
     * @param $sequences
     * @return string
     */
    protected function fileMissingNotifications($sequences): string
    {
        $table = '<table border="1" style="background-color: #fee; border-collapse: collapse; width: 650px; text-align: center; font-family: Aptos, serif; font-size: 14px;">';
        $table .= '<tr style="height: 30px;"><th colspan="4"><b style="color: red; font-size: 16px;">CDR file missing info</b></th></tr>';
        $table .= '<tr style="height: 25px;"><th>Platform Name</th><th>Switch name</th><th colspan="2">Date range</th><th>File sequence no</th></tr>';
        foreach ($sequences as $key => $sequence) {
            $cdrFileInfo = explode(',', $key);
            $styles = ($cdrFileInfo[0] === 'IGW') ? "style='height: 25px; background: #DEF4FE;'" : "style='height: 25px; background: #F8DEFE'";
            $table .= "<tr $styles>";
            foreach ($cdrFileInfo as $info) {
                $table .= '<td>' . $info . '</td>';
            }
            // Assuming $sequence is already an array containing the file sequence numbers
            $table .= '<td>' . implode(', ', $sequence) . '</td>';

            $table .= '</tr>';
        }
        $table .= '</table>';

        return $table;
    }



}
