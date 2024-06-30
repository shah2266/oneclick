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
        //$query = /** @lang text */ "SELECT * FROM $table WHERE traffic_date BETWEEN '$fromDate' AND '$toDate' ORDER BY $dateColumn";
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
                SUM(cm.CallDuration) / 60 AS 'duration',
                SUM(cm.BillDuration) / 60 AS 'bill_duration'
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

    protected function fetchDestinationWiseProfitLossFromIgw($table, $dateColumn, $fromDate, $toDate): array
    {
        //$query = /** @lang text */ "SELECT * FROM $table WHERE traffic_date BETWEEN '$fromDate' AND '$toDate' ORDER BY $dateColumn";
        $query =
            /** @lang text */
            "
            SELECT
                CONVERT(VARCHAR(30), cm.$dateColumn, 112) AS 'order_date',
                CONVERT(VARCHAR(30), cm.$dateColumn, 106) AS 'traffic_date',
                cy.ShortName AS 'os_name',
                ct.CountryName AS 'country',
                z.ZoneName AS 'destination',
                cm.InRatedPrefix AS 'btrc_zone_code',
                cm.OutRatedPrefix AS 'os_zone_code',
                " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'successful_call',
                SUM(cm.CallDuration/60) AS 'duration',
                SUM(cm.BillDuration/60) AS 'bill_duration',
                SUM(cm.CallDuration/60 * cm.InRate) / SUM(cm.CallDuration/60) AS 'in_rate',
                SUM(cm.CallDuration/60 * cm.OutRate) / SUM(cm.CallDuration/60) AS 'out_rate',
                SUM(cm.CallDuration/60 * cm.InRate) AS 'btrc_y_amount_in_dollar',
                SUM(cm.CallDuration/60 * cm.OutRate) AS 'os_y_amount_in_dollar',
                SUM(cm.BillDuration/60 * cm.InRate) AS 'btrc_y_bill_amount_in_dollar',
                SUM(cm.BillDuration/60 * cm.InFixedRate) AS 'btrc_x_amount_in_bdt',
                ex.Rate AS 'exchange_rate',
                (SUM(cm.CallDuration/60 * cm.InRate) - SUM(cm.CallDuration/60 * cm.OutRate)) AS 'btrc_os_y_amount_in_dollar',
                SUM(cm.BillDuration/60 * cm.InRate) * ex.Rate AS 'btrc_y_bill_amount_in_bdt',
                SUM(cm.BillDuration/60 * cm.InFixedRate) - SUM(cm.BillDuration/60 * cm.InRate) * ex.Rate AS 'z_amount_in_bdt',
                (SUM(cm.BillDuration/60 * cm.InFixedRate) - SUM(cm.BillDuration/60 * cm.InRate) * ex.Rate) * 0.15 + SUM(cm.BillDuration/60 * cm.InRate) * ex.Rate AS 'invoice_amount_in_bdt',
                (SUM(cm.BillDuration/60 * cm.InFixedRate) - SUM(cm.BillDuration/60 * cm.InRate) * ex.Rate) * 0.15 * 0.40 AS 'btrc_part',
                SUM(cm.CallDuration/60 * cm.OutRate) * ex.Rate AS 'os_y_amount_in_bdt',
                ((((Sum(cm.BillDuration/60*cm.InFixedRate) - (Sum(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15) + (Sum(cm.BillDuration/60*cm.InRate) * ex.Rate)) - (((Sum(cm.BillDuration/60*cm.InFixedRate) - (Sum(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15 * 0.40) + (Sum(cm.CallDuration/60*cm.OutRate) * ex.Rate)))  AS 'actual_amount_in_bdt'
            FROM
                $table cm
                JOIN ExchangeRate ex ON cm.$dateColumn BETWEEN ex.FromActiveDate AND ex.ToActiveDate
                JOIN Zone z ON z.ZoneCode = cm.InRatedPrefix
                JOIN Company cy ON cy.CompanyID = cm.OutCompanyID
                JOIN Country ct ON z.CountryID = ct.CountryID
            WHERE
                cm.$dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
                AND cm.ReportTrafficDirection = 2
            GROUP BY
                CONVERT(VARCHAR(30), cm.$dateColumn, 112),
                CONVERT(VARCHAR(30), cm.$dateColumn, 106),
                cy.ShortName,
                ct.CountryName,
                z.ZoneName,
                cm.InRatedPrefix,
                cm.OutRatedPrefix,
                ex.Rate,
                cm.InFixedRate
            ORDER BY
                CONVERT(VARCHAR(30), cm.$dateColumn, 112), cy.ShortName;
        ";

        $data = $this->QueryExecuted('sqlsrv1', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
    }

    /**
     * @param $table
     * @param $dateColumn
     * @param $fromDate
     * @param $toDate
     * @return array
     */
    protected function fetchDayWiseProfitLoss($table, $dateColumn, $fromDate, $toDate): array
    {
        $query =
            /** @lang text */
            "
            SELECT
                CONVERT(VARCHAR(30),cm.$dateColumn,112) AS 'order_date',
                REPLACE(CONVERT(VARCHAR(30),cm.$dateColumn, 6),' ','-') AS 'traffic_date',
                " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'successful_call',
                SUM(cm.CallDuration/60) AS 'duration' ,
                SUM(cm.BillDuration/60) AS 'bill_duration',
                ex.Rate AS 'exchange_rate',
                ((((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15) + (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) - (((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15 * 0.40) + (SUM(cm.CallDuration/60*cm.OutRate) * ex.Rate)))  AS 'actual_amount_bdt'
            from
                $table cm,
                ExchangeRate ex
            Where
                cm.$dateColumn between '$fromDate 00:00:00' AND '$toDate 23:59:59'
                AND cm.ReportTrafficDirection=2
                AND cm.$dateColumn BETWEEN ex.FromActiveDate AND ex.ToActiveDate
            Group by
                convert(VARCHAR(30),cm.$dateColumn,112),
                convert(VARCHAR(30),cm.$dateColumn, 6) ,ex.Rate
            Order BY
                convert(VARCHAR(30),cm.$dateColumn,112)
            ";

        $data = $this->QueryExecuted('sqlsrv1', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
    }


    /**
     * @param $table
     * @param $dateColumn
     * @param $fromDate
     * @param $toDate
     * @param $subFromDate
     * @param $subToDate
     * @return array
     */
    protected function fetchAndCompareDayWiseProfitLoss($table, $dateColumn, $fromDate, $toDate, $subFromDate, $subToDate): array
    {
        $query =
            /** @lang text */
            "
                SELECT
                    cm.tDate AS 'cm_traffic_date', cm.sCall AS 'cm_successful_call' , cm.dur AS 'cm_duration', cm.bDur AS 'cm_bill_duration', cm.amt_bdt AS 'cm_actual_amount_bdt',
                    (cm.amt_bdt - pm.amt_bdt) AS 'diff_amt',
                    pm.tDate AS 'pm_traffic_date', pm.sCall AS 'pm_successful_call', pm.dur AS 'pm_duration', pm.bDur AS 'pm_bill_duration', pm.amt_bdt AS 'pm_actual_amount_bdt'
                    FROM (
                        SELECT
                            ROW_NUMBER() OVER (ORDER BY CONVERT(VARCHAR(30), cm.$dateColumn, 112)) AS serial_number,
                            CONVERT(VARCHAR(30), cm.$dateColumn, 112) AS 'order_date',
                            REPLACE(CONVERT(VARCHAR(30), cm.$dateColumn, 6), ' ', '-') AS 'tDate',
                            " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'sCall',
                            SUM(cm.CallDuration/60) AS 'dur' ,
                            SUM(cm.BillDuration/60) AS 'bDur',
                            ((((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15) + (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) - (((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15 * 0.40) + (SUM(cm.CallDuration/60*cm.OutRate) * ex.Rate)))  AS 'amt_bdt'
                        FROM
                            $table cm
                        JOIN
                            ExchangeRate ex ON cm.$dateColumn BETWEEN ex.FromActiveDate AND ex.ToActiveDate
                        WHERE
                            cm.$dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
                            AND cm.ReportTrafficDirection = 2
                        GROUP BY
                            CONVERT(VARCHAR(30), cm.$dateColumn, 112),
                            REPLACE(CONVERT(VARCHAR(30), cm.$dateColumn, 6), ' ', '-'),
                            ex.Rate
                    ) AS cm,
                    (
                        SELECT
                            ROW_NUMBER() OVER (ORDER BY CONVERT(VARCHAR(30), cm.$dateColumn, 112)) AS serial_number,
                            CONVERT(VARCHAR(30), cm.$dateColumn, 112) AS 'order_date',
                            REPLACE(CONVERT(VARCHAR(30), cm.$dateColumn, 6), ' ', '-') AS 'tDate',
                            " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'sCall',
                            SUM(cm.CallDuration/60) AS 'dur' ,
                            SUM(cm.BillDuration/60) AS 'bDur',
                            ((((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15) + (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) - (((SUM(cm.BillDuration/60*cm.InFixedRate) - (SUM(cm.BillDuration/60*cm.InRate) * ex.Rate)) * 0.15 * 0.40) + (SUM(cm.CallDuration/60*cm.OutRate) * ex.Rate)))  AS 'amt_bdt'
                        FROM
                            $table cm
                        JOIN
                            ExchangeRate ex ON cm.$dateColumn BETWEEN ex.FromActiveDate AND ex.ToActiveDate
                        WHERE
                            cm.$dateColumn BETWEEN '$subFromDate 00:00:00' AND '$subToDate 23:59:59'
                            AND cm.ReportTrafficDirection = 2
                        GROUP BY
                            CONVERT(VARCHAR(30), cm.$dateColumn, 112),
                            REPLACE(CONVERT(VARCHAR(30), cm.$dateColumn, 6), ' ', '-'),
                            ex.Rate
                    ) AS pm
                where cm.serial_number=pm.serial_number
                GROUP BY
                    cm.order_date, cm.tDate, cm.sCall, cm.dur, cm.bDur, cm.amt_bdt,
                    pm.order_date, pm.tDate, pm.sCall, pm.dur, pm.bDur, pm.amt_bdt
                ORDER BY
                    cm.order_date, pm.order_date
            ";

        $data = $this->QueryExecuted('sqlsrv1', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
    }

    /**
     * @param $table
     * @param $dateColumn
     * @param $fromDate
     * @param $toDate
     * @return array
     */
    protected function fetchIosWiseIncomingFromIgw($table, $dateColumn, $fromDate, $toDate): array
    {
        $query = /** @lang text */
            "
            SELECT
                CONVERT(varchar(7), cm.$dateColumn, 126) AS 'month_name',
                cy.ShortName AS 'company_name',
                " . ($table === 'CDR_MAIN' ? "COUNT(*)" : "SUM(SuccessfulCall)") . " AS 'successful_call',
                SUM(cm.CallDuration) / 60 AS 'duration',
                SUM(cm.BillDuration) / 60 AS 'bill_duration'
            FROM
                $table cm, Company cy
            WHERE
                cm.$dateColumn BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'
                AND cm.ReportTrafficDirection = 1
                AND cm.OutCompanyID = cy.CompanyID
            GROUP BY
                CONVERT(varchar(7), cm.$dateColumn, 126),
                cm.OutCompanyID,
                cy.ShortName
            ORDER BY
                cy.ShortName;
            ";

        $data = $this->QueryExecuted('sqlsrv1', $query);

        // Get the total count
        $totalCount = count($data);

        return ['data' => $data, 'total_count' => $totalCount];
    }


    /**
     * @param $file_name
     * @return bool
     */
    protected function isFileUnique($file_name): bool
    {
        $query = /** @lang text */
            "SELECT file_name FROM cdr_files WHERE file_name = '$file_name'";

        // Query the database to check if the file already exists
        $existingFiles = $this->QueryExecuted('mysql8', $query);
        return empty($existingFiles);
    }

    protected function deleteNoneUniqueFileRecords($file_name): bool
    {
        preg_match('/\.(\d+)$/', $file_name, $matches);
        $sequence_no = $matches[1];

        $raw_records = /** @lang text */ "DELETE FROM bicx_cdr_main
                        WHERE created_at BETWEEN '" . $this->getYesterday() . " 00:00:00' AND '" . $this->getToday() . " 23:59:59'
                        AND file_sequence_no = $sequence_no";

        $file_records = /** @lang text */ "DELETE FROM cdr_files WHERE file_sequence_no = $sequence_no";
        $this->QueryExecuted('mysql8', $raw_records);
        $this->QueryExecuted('mysql8', $file_records);

        return true;
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

    protected function insertOperation($table, $query)
    {
        DB::connection('mysql8')->table($table)->insert($query);
    }

}
