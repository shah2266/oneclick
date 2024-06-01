<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;

trait BanglaICXCdrFileProcessorTrait
{

    private $columnsToDisplay = [1, 3, 9, 10, 11, 18, 26, 31, 32, 38, 39, 44, 49, 50, 56, 57, 92, 125];

    public function processCdrRecord($sourceFilePath, $destinationDir)
    {
        $table = 'BICX_CDR_MAIN';
        $batchSize = 100; // Number of rows to process in each batch

        try {
            $sourceFileObject = new SplFileObject($sourceFilePath, 'r');
            $sourceFileObject->setFlags(SplFileObject::READ_CSV);
            $sourceFileObject->setCsvControl(',', '"', '\\');
        } catch (RuntimeException $e) {
            // Handle file opening error
            return;
        }

        // Skip the first line (header) of the CSV file
        $sourceFileObject->seek(1);

        $batch = [];
        $rowCount = 0;

        while (!$sourceFileObject->eof()) {
            $row = $sourceFileObject->fgetcsv();

            if (empty($row) || $row[0] === null) {
                continue; // Skip empty lines
            }

            $filteredRow = $this->filterColumns($row);
            $batch[] = $this->prepareDataForInsertion($sourceFilePath, $filteredRow);

            $rowCount++;

            if ($rowCount % $batchSize === 0) {
                $this->insertOperation($table, $batch);
                $batch = []; // Reset batch array
            }
        }

        // Insert remaining rows
        if (!empty($batch)) {
            $this->insertOperation($table, $batch);
        }

        // Move the processed file to the destination directory
        $this->writeBackupFile($sourceFileObject, $sourceFilePath, $destinationDir);

        // Delete the original file after processing
        $this->deleteOriginalFile($sourceFilePath);
    }

    /**
     * @param null $file
     * @param $row
     * @return array
     */
    protected function prepareDataForInsertion($file, $row): array
    {
        $data = $this->cdrFiles($file);
        return [
            'cdr_sequence' => $row[0],
            'status' => $row[1],
            'release_direction' => $row[2],
            'sip_status_code' => $row[3],
            'internal_reason' => $row[4],
            'duration' => $row[5],
            'ingress_call_info_zone_name' => $row[6],
            'ingress_call_info_calling_party' => $row[7],
            'ingress_call_info_called_party' => $row[8],
            'ingress_call_info_inviting_ts' => $this->convertToTimestamp($row[9]),
            'ingress_call_info_ringing_ts' => $this->convertToTimestamp($row[10]),
            'egress_call_info_zone_name' => $row[11],
            'egress_call_info_calling_party' => $row[12],
            'egress_call_info_called_party' => $row[13],
            'egress_call_info_inviting_ts' => $this->convertToTimestamp($row[14]),
            'egress_call_info_ringing_ts' => $this->convertToTimestamp($row[15]),
            'ingress_media_record_codec_used' => $row[16],
            'egress_media_record_codec_used' => $row[17],
            'cdr_file_name'         => $data['file_name'],
            'file_sequence_no'  => $data['file_sequence_no'],
            'switch_id'         => $data['switch_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @param $filePath
     * @return array
     */
    private function readFile($filePath): array
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        array_shift($lines); // Remove the first line

        $data = [];
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $data[] = explode(",", $line);
            }
        }

        return $data;
    }

    /**
     * @param $sourceFileObject
     * @param $sourceFilePath
     * @param $destinationDir
     * @return void
     */
    public function writeBackupFile($sourceFileObject, $sourceFilePath, $destinationDir)
    {
        $newFilePath = $destinationDir . DIRECTORY_SEPARATOR . basename($sourceFilePath);
        $newFile = new SplFileObject($newFilePath, 'w');

        // Reset file pointer to the start
        $sourceFileObject->rewind();
        $sourceFileObject->seek(1); // Skip the first line again

        while (!$sourceFileObject->eof()) {
            $row = $sourceFileObject->fgetcsv();
            if (empty($row) || $row[0] === null) {
                continue; // Skip empty lines or EOF
            }

            $filteredRow = $this->filterColumns($row);
            $line = implode(',', $filteredRow) . ";\n";
            $newFile->fwrite($line); // Write CSV data to the new file
        }
    }


    /**
     * @param $row
     * @return array
     */
    private function filterColumns($row): array
    {
        $filteredRow = [];
        foreach ($this->columnsToDisplay as $columnIndex) {
            $columnIndexAdjusted = $columnIndex - 1;
            $filteredRow[] = $row[$columnIndexAdjusted] ?? '';
        }
        return $filteredRow;
    }

    /**
     * @param $filePath
     */
    private function deleteOriginalFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * @param $file_path
     * @return true
     */
    private function processCdrFileInfo($file_path): bool
    {
        $table = 'cdr_files';
        //$data = $filesDetails = explode(',', $this->cdrFiles($file_path));
        $data = $this->cdrFiles($file_path);

        // Check file already exists
        if (!$this->isFileUnique($data['file_name'])) {
            return false;
        }

        $query = [
            'file_name'         => $data['file_name'],
            'file_sequence_no'  => $data['file_sequence_no'],
            'file_status'       => $data['file_status'],
            'file_date_time'    => $data['file_date_time'],
            'file_size'         => $data['file_size'],
            'switch_id'         => $data['switch_id'],
            'created_at'        => NOW(),
            'updated_at'        => NOW()
        ];

        $this->insertOperation($table, $query);
        return true;
    }

    /**
     * @param $file_path
     * @return array|null
     */
    private function cdrFiles($file_path): ?array
    {
        $file_name      = basename($file_path);
        $file_size      = filesize($file_path);
        $file_sequence  = basename($file_path, '.txt');

        $startPos       = strpos($file_name, '.', strpos($file_name, '.') + 1);
        $file_date_time = $this->convertToTimestamp(substr($file_name, $startPos + 1, 14));

        $file_status    = 1;  // 1 - import;
        $switch_id      = 1;  // 1 - Cataleya;

        if (preg_match('/\.(\d+)$/', $file_sequence, $matches)) {
            return [
                'file_name'         => $file_name,
                'file_sequence_no'  => $matches[1],
                'file_status'       => $file_status,
                'file_date_time'    => $file_date_time,
                'file_size'         => $file_size .' kb',
                'switch_id'         => $switch_id
            ];
        }

        return null;
    }

    /**
     * @param $file_name
     * @return bool
     */
    private function isFileUnique($file_name): bool
    {
        $query = /** @lang text */
            "SELECT file_name FROM cdr_files WHERE file_name = '$file_name'";

        // Query the database to check if the file already exists
        $existingFiles = $this->QuerySelectOperation('mysql8', $query);
        return empty($existingFiles);
    }

    /**
     * @return string[]
     */
    private function switchName(): array
    {
        return [
            1 => 'Cataleya'
        ];
    }


    /**
     * @param $value
     * @return string|null
     */
//    private function convertToTimestamp($value): ?string
//    {
//        if (!empty($value) && strlen($value) >= 14) {
//            try {
//                // Assuming format is YYYYMMDDHHMMSSfff
//                $year = substr($value, 0, 4);
//                $month = substr($value, 4, 2);
//                $day = substr($value, 6, 2);
//                $hour = substr($value, 8, 2);
//                $minute = substr($value, 10, 2);
//                $second = substr($value, 12, 2);
//                $millisecond = substr($value, 14, 3);
//
//                // Validate datetime components
//                if (checkdate($month, $day, $year) && $hour <= 23 && $minute <= 59 && $second <= 59) {
//                    // Create a datetime string
//                    $dateTime = "$year-$month-$day $hour:$minute:$second.$millisecond";
//                    dump("Constructed DateTime: $dateTime");
//                    // Parse datetime string to Carbon instance
//                    return Carbon::createFromFormat('Y-m-d H:i:s.u', $dateTime)->toDateTimeString();
//                } else {
//
//                    return null; // Return null if datetime components are invalid
//                }
//            } catch (\Exception $e) {
//                error_log("Parsing error: " . $e->getMessage());
//                return null; // Return null if there is a parsing error
//            }
//        }
//        return null;
//    }

    private function convertToTimestamp($value): ?string
    {
        if (!empty($value) && strlen($value) >= 14) {
            try {
                // Extract datetime components
                $year = substr($value, 0, 4);
                $month = substr($value, 4, 2);
                $day = substr($value, 6, 2);
                $hour = substr($value, 8, 2);
                $minute = substr($value, 10, 2);
                $second = substr($value, 12, 2);

                // Check if milliseconds are present
                if (strlen($value) > 14) {
                    // Extract milliseconds
                    $millisecond = substr($value, 14, 3);
                } else {
                    $millisecond = '000'; // If milliseconds are not present, set to '000'
                }

                // Validate datetime components
                if (checkdate($month, $day, $year) && $hour <= 23 && $minute <= 59 && $second <= 59) {
                    // Construct a datetime string
                    $dateTime = "$year-$month-$day $hour:$minute:$second.$millisecond";

                    // Parse datetime string to Carbon instance
                    return Carbon::createFromFormat('Y-m-d H:i:s.u', $dateTime)->toDateTimeString();
                } else {
                    return null; // Return null if datetime components are invalid
                }
            } catch (\Exception $e) {
                return null; // Return null if there is a parsing error
            }
        }
        return null; // Return null if the input value is empty or shorter than 14 characters
    }

    private function QuerySelectOperation(string $connectionName, string $query): array
    {
        return DB::connection($connectionName)->select($query);
    }


    private function insertOperation($table, $query)
    {
        DB::connection('mysql8')->table($table)->insert($query);
    }
}
