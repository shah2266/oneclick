<?php

namespace App\Query;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataCrossCheckQuery
{

    /**
     * @return string[]
     */
    private function setting(): array
    {
        return array(1=>'sqlsrv1', 2=>'sqlsrv1', 3=>'sqlsrv2', 4=>'sqlsrv2');
    }

    /**
     * @return array
     */
    private function dataProcessing(): array
    {
        $data = array();

        foreach($this->setting() as $key=>$conn) {
            if($key < 3) {
                array_push($data,$this->queryProcess($conn, $key));
            } else {
                array_push($data,$this->queryProcess($conn, ($key-2)));
            }
        }

        return $data;
    }

    /**
     * @param $conn
     * @param $direction
     * @return array
     */
    private function queryProcess($conn, $direction): array
    {
        $fromDate = Carbon::now()->subDays(3)->format('Ymd');
        $toDate = Carbon::now()->format('Ymd');

        return DB::connection($conn)
            ->select("
            SELECT m.mcDate, m.msCall, m.mDur, (m.msCall - s.ssCall) AS sDiff, (m.mDur - s.sDur) AS dDiff, s.ssCall, s.sDur
            FROM (
                SELECT convert(VARCHAR(30),cm.ConnectionTime,112) as mcTime, CONVERT(varchar,cm.ConnectionTime,6) as mcDate,
                       count(*) as msCall, Round(SUM(cm.CallDuration)/60, 2, 0) as mDur, (SUM(cm.CallDuration)/60)/count(*) as mACD
                from CDR_MAIN cm
                Where cm.ConnectionTime between '$fromDate 00:00:00' And '$toDate 23:59:59'
                and cm.ReportTrafficDirection= $direction
                GROUP BY convert(VARCHAR(30),cm.ConnectionTime,112),CONVERT(varchar,cm.ConnectionTime,6)
            ) m,
            (
                SELECT convert(VARCHAR(30),cs.TrafficDate,112) as scTime,
                convert(VARCHAR(30),cs.TrafficDate,6) as scDate,
                SUM(cs.SuccessfulCall) as ssCall, Round(SUM(cs.CallDuration)/60, 2, 0) as sDur, (SUM(cs.CallDuration)/60)/SUM(cs.SuccessfulCall) as sACD
                from CallSummary cs
                Where cs.TrafficDate between '$fromDate 00:00:00' And '$toDate 23:59:59'
                and cs.ReportTrafficDirection= $direction
                GROUP BY convert(VARCHAR(30),cs.TrafficDate,112),convert(VARCHAR(30),cs.TrafficDate,6)
            ) s
            WHERE m.mcDate = s.scDate
            ORDER BY m.mcTime
        ");
    }


    /**
     * @return array
     */
    public static function dataChecking(): array
    {
        return (new self)->dataProcessing();
    }

}
