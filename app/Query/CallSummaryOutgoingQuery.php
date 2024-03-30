<?php
namespace App\Query;
use Illuminate\Support\Facades\DB;
class CallSummaryOutgoingQuery
{
	/**
     * DB Query
     */
    public static function OSWiseOutgoing($getFromDate, $getToDate) {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName', DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
					DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=2)) AS  'successfulCallsPercent'"),
					DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=2) AS 'totalDurationPercent'") )
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',2)
				->groupBy('c.ShortName')
				->orderBy('c.ShortName')
				->get();
    }

    public static function IOSWiseOutgoing($getFromDate, $getToDate) {
        //dd($fromDate.' '.$toDate);
        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) AS TrafficDate"),
					DB::raw("SUM(cm.SuccessfulCall) AS 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) AS Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
					DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=2)) AS  'successfulCallsPercent'"),
					DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
					from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
					and cm.ReportTrafficDirection=2) AS 'totalDurationPercent'") )
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',2)
				->groupBy('c.ShortName', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy('c.ShortName')
				->get();
    }

    public static function DesWiseOutgoing($getFromDate, $getToDate) {

		return DB::connection('sqlsrv1')
					->select("SELECT c.ShortName, cn.CountryName 'Country', z.ZoneName 'Destination',cm.InRatedPrefix 'DestinationCode', SUM(cm.SuccessfulCall) 'SuccessfulCall', (SUM(cm.CallDuration)/60) 'Duration' , (SUM(cm.BillDuration)/60) 'BillDuration'
					from CallSummary cm, Company c,Country cn, Zone z Where cm.TrafficDate between '$getFromDate' And '$getToDate' AND cm.OutCompanyID = c.CompanyID AND z.CountryID = cn.CountryID AND z.ZoneCode=cm.InratedPrefix AND cm.ReportTrafficDirection=2 Group by c.ShortName,cn.CountryName,z.ZoneName,cm.InRatedPrefix Order by c.ShortName");
    }

    public static function DailyOutgoing($getToDate2) {

		$getFromDate = \Carbon\Carbon::parse($getToDate2)->subDays(35)->format('Ymd').' 00:00:00';
		$getToDate = \Carbon\Carbon::parse($getToDate2)->format('Ymd').' 23:59:59';

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',2)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
	}

	public static function dayWiseOutgoing($getFromDate, $getToDate) {

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',2)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
	}


	//Outgoing day wise report with bill duration
	public static function dayWiseOutgoingWithBillDuration($getFromDate, $getToDate) {

        return DB::connection('sqlsrv1')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as traffic_date"),DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.BillDuration)/60) as Duration"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=',2)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
	}


}
