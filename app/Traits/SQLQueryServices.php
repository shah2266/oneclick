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
            $dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
            AND ReportTrafficDirection = '$direction'
        GROUP BY
            CONVERT(VARCHAR(30), $dateColumn, 112)
        ORDER BY
            CONVERT(VARCHAR(30), $dateColumn, 112)
        ";

        return $this->QueryExecuted($connectionName, $query);
    }


    /**
     * Fetch icx and ans incoming data from the Btrac IOS database.
     *
     * @param $table
     * @param $dateColumn
     * @param $fromDate
     * @param $toDate
     * @param $direction
     * @param $companyID
     * @param $joinColumn
     * @return array
     */
    public function fetchIcxAndAnsData($table, $dateColumn, $fromDate, $toDate, $direction, $companyID, $joinColumn): array
    {
        $query = /** @lang text */
            "
            SELECT
                CONVERT(VARCHAR(7), cm.$dateColumn, 126) AS month,
                inCom.ShortName AS inCompany,
                outCom.ShortName AS outCompany,
                " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS successfulCall,
                SUM(cm.CallDuration) / 60 AS duration,
                SUM(cm.BillDuration) / 60 AS billDuration
            FROM
                $table cm, Company inCom, Company outCom
            WHERE
                cm.$dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
                AND cm.ReportTrafficDirection = $direction
                AND " . ($direction == 1 ? "cm.InCompanyID": "cm.OutCompanyID") ." IN ($companyID)
                AND " . ($direction == 1 ? "cm.InCompanyID = inCom.CompanyID" : "cm.OutCompanyID = outCom.CompanyID") . "
                AND cm.$joinColumn = " . ($direction == 1 ? "outCom.CompanyID" : "inCom.CompanyID") . "
            GROUP BY
                CONVERT(VARCHAR(7), cm.$dateColumn, 126), inCom.ShortName, outCom.ShortName
            ORDER BY
                outCom.ShortName ASC;
            ";

        $data = $this->QueryExecuted('sqlsrv2', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
    }

    public function fetchDestinationWiseDataFromIos($table, $dateColumn, $fromDate, $toDate): array
    {

        $query =
            /** @lang text */
        "
            SELECT
                CONVERT(VARCHAR(30), cm.$dateColumn, 112) AS 'order_date',
                CONVERT(VARCHAR(30), cm.$dateColumn, 106) AS 'traffic_date',
                inCom.ShortName AS 'icx_name',
                inRoute.RouteName AS 'icx_route_name',
                outCom.ShortName AS 'igw_name',
                outRoute.RouteName AS 'igw_route_name',
                c.CountryName AS 'country',
                z.ZoneName AS 'destination',
                cm.InRatedPrefix AS 'destination_code',
                " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'successful_call',
                SUM(cm.CallDuration) AS 'duration',
                SUM(cm.BillDuration) AS 'bill_duration'
            FROM
                $table cm,
                Company inCom,
                Company outCom,
                ROUTE inRoute,
                ROUTE outRoute,
                Zone z,
                Country c
            WHERE
                cm.$dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
                AND cm.ReportTrafficDirection = 2
                AND cm.InCompanyID = inCom.CompanyID
                AND cm.OutCompanyID = outCom.CompanyID
                AND cm.IncomingRouteID = inRoute.RouteID
                AND cm.OutgoingRouteID = outRoute.RouteID
                AND z.ZoneCode = cm.InRatedPrefix
                AND z.CountryID = c.CountryID
            GROUP BY
                CONVERT(VARCHAR(30), cm.$dateColumn, 112),
                CONVERT(VARCHAR(30), cm.$dateColumn, 106),
                inCom.ShortName,
                inRoute.RouteName,
                outCom.ShortName,
                outRoute.RouteName,
                c.CountryName,
                z.ZoneName,
                cm.InRatedPrefix
            ORDER BY
            CONVERT(VARCHAR(30), cm.$dateColumn, 112);
        ";

        $data = $this->QueryExecuted('sqlsrv2', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
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
