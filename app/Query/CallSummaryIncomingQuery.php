<?php
namespace App\Query;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
class CallSummaryIncomingQuery
{
	/**
     * DB Query
     */
    public static function OSWiseIncoming($getFromDate, $getToDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
					DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=1)) AS  'successfulCallsPercent'"),
					DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=1) AS 'totalDurationPercent'") )
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy('c.ShortName')
				->orderBy('c.ShortName')
				->get();
    }

    public static function OSIPWiseIncoming($getFromDate, $getToDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName','cm.OriginationIPAddress as IPAddress', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy('c.ShortName', 'cm.OriginationIPAddress')
				->orderBy('c.ShortName')
				->get();
    }

    public static function IOSWiseIncoming($getFromDate, $getToDate): Collection
    {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) AS TrafficDate"),
					DB::raw("SUM(cm.SuccessfulCall) AS 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) AS Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
					DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=1)) AS  'successfulCallsPercent'"),
					DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=1) AS 'totalDurationPercent'") )
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy('c.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy('c.ShortName')
				->get();
    }

    public static function ANSWiseIncoming($getFromDate, $getToDate): Collection
    {

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.ANSID', '=', 'c.CompanyID')
				->select('c.ShortName',DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy('c.ShortName')
				->orderBy('c.ShortName')
				->get();
    }

    public static function DailyIncoming($getToDate2): Collection
    {

		$getFromDate = Carbon::parse($getToDate2)->subDays(35)->format('Ymd').' 00:00:00';
		$getToDate = Carbon::parse($getToDate2)->format('Ymd').' 23:59:59';

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
	}

	public static function dayWiseIncoming($getFromDate, $getToDate): Collection
    {

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
    }

    public static function TDMWiseIncoming($getToDate2): array
    {

        $getFromDate = Carbon::parse($getToDate2)->subDays(35)->format('Ymd').' 00:00:00';
		$getToDate = Carbon::parse($getToDate2)->format('Ymd').' 23:59:59';

//        return DB::connection('sqlsrv1')->table('CallSummary as cm')
//				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
//					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
//					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
//				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
//				->where('cm.ReportTrafficDirection','=',1)
//				->where('cm.OriginationIPAddress','=','')
//				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
//				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
//				->get();

        return DB::connection('sqlsrv1')
                ->select("SELECT convert(VARCHAR(30),t1.TrafficDate,112), substring(convert(VARCHAR,t1.TrafficDate,106),0,14) 'traffic_date', ISNULL(t2.SuccessfulCall,0) 'SuccessfulCall', ISNULL(t2.CallDuration,0) 'Duration', ISNULL(t2.ACD,0) 'ACD'
                    from CallSummary t1
                    LEFT JOIN (
                        SELECT convert(VARCHAR(30),t2.TrafficDate,112) date2, sum(t2.SuccessfulCall) SuccessfulCall,
                        SUM(t2.CallDuration/60) CallDuration,round(SUM(t2.CallDuration/60)/sum(t2.SuccessfulCall),2) 'ACD'
                        from CallSummary t2 Where t2.TrafficDate BETWEEN '$getFromDate' And '$getToDate'
                        AND t2.ReportTrafficDirection=1 AND t2.OriginationIPAddress = ''
                        GROUP BY convert(VARCHAR(30),t2.TrafficDate,112)
                    ) t2 ON convert(VARCHAR(30),t1.TrafficDate,112) = date2
                    Where t1.TrafficDate BETWEEN '$getFromDate' And '$getToDate'
                    AND t1.ReportTrafficDirection=1
                    AND t1.OriginationIPAddress != ''
                    GROUP BY convert(VARCHAR(30),t1.TrafficDate,112),substring(convert(VARCHAR,t1.TrafficDate,106),0,14), t2.SuccessfulCall, t2.CallDuration, t2.ACD
                    ORDER BY 1");
    }

	public static function IPWiseIncoming($getToDate2): Collection
    {

        $getFromDate = Carbon::parse($getToDate2)->subDays(35)->format('Ymd').' 00:00:00';
		$getToDate = Carbon::parse($getToDate2)->format('Ymd').' 23:59:59';

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',1)
				->where('cm.OriginationIPAddress','!=','')
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
    }
}
