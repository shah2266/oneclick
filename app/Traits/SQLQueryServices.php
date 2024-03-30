<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SQLQueryServices
{
    /**
     * Fetch data from the database.
     *
     * @param string $connectionName
     * @param string $table
     * @param string $fromDate
     * @param string $toDate
     * @param string $direction
     * @param string $dateColumn
     * @return array
     */
    public function fetchData(string $connectionName, string $table, string $fromDate, string $toDate, string $direction, string $dateColumn): array
    {
        $query = /** @lang text */
            "
        SELECT
            CONVERT(VARCHAR(30), $dateColumn, 112) AS date,
            " .($table == 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS successfulCall,
            SUM(CallDuration) / 60 AS duration
        FROM
            $table
        WHERE
            $dateColumn BETWEEN '{$fromDate} 00:00:00' AND '{$toDate} 23:59:59'
            AND ReportTrafficDirection = '{$direction}'
        GROUP BY
            CONVERT(VARCHAR(30), $dateColumn, 112)
        ORDER BY
            CONVERT(VARCHAR(30), $dateColumn, 112)
        ";

        return $this->QueryExecuted($connectionName, $query);
    }

    /**
     * @param string $connectionName
     * @param string $query
     * @return array
     */
    private function QueryExecuted(string $connectionName, string $query): array
    {
        return DB::connection($connectionName)->select($query);
    }

}
