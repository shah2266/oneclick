<?php
namespace App\Query;
use Illuminate\Support\Facades\DB;
class BanglaicxReportQuery
{
	/**
     * DB Query
     */

	/**
	 * Direction
	 * 1 - Incoming
	 * 2 - Outgoing
	 * 3 - National or Domestic
	 */

	/**
	 * DB connection
	 * 'sqlsrv5' is Banglaicx db connection string
	 */

	//Origination report query
    public function originationQuery($getFromDate, $getToDate, $direction) {

        return DB::connection('sqlsrv5')->table('CallSummary as cm')
				->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName',
						DB::raw("SUM(cm.SuccessfulCall) AS 'SuccessfulCall'"),
						DB::raw("(SUM(cm.CallDuration)/60) AS Duration"),
						DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
						DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
							from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
							and cm.ReportTrafficDirection= $direction)) AS  'successfulCallsPercent'"),
						DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
							from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
							and cm.ReportTrafficDirection= $direction) AS 'totalDurationPercent'"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=', $direction)
				->groupBy('c.ShortName')
				->orderBy('c.ShortName')
				->get();
	}

	//Termination report query
    public function terminationQuery($getFromDate, $getToDate, $direction) {

        return DB::connection('sqlsrv5')->table('CallSummary as cm')
				->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
				->select('c.ShortName',
						DB::raw("SUM(cm.SuccessfulCall) AS 'SuccessfulCall'"),
						DB::raw("(SUM(cm.CallDuration)/60) AS Duration"),
						DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"),
						DB::raw("(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall
							from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
							and cm.ReportTrafficDirection= $direction)) AS  'successfulCallsPercent'"),
						DB::raw("(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration
							from CallSummary cm Where cm.TrafficDate between '$getFromDate' And '$getToDate'
							and cm.ReportTrafficDirection= $direction) AS 'totalDurationPercent'"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=', $direction)
				->groupBy('c.ShortName')
				->orderBy('c.ShortName')
				->get();
	}

	//Origination and Termination report query
	public function originationAndTerminationQuery($getFromDate, $getToDate, $direction)
	{
		return DB::connection('sqlsrv5')->table('CallSummary as cm')
				->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
				->join('Company as oc', 'cm.OutCompanyID', '=', 'oc.CompanyID')
				->select('c.ShortName as ShortNameOne', 'oc.ShortName as ShortNameTwo',
						DB::raw("SUM(cm.SuccessfulCall) AS 'SuccessfulCall'"),
						DB::raw("(SUM(cm.CallDuration)/60) AS Duration"),
						DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=', $direction)
				->groupBy('c.ShortName', 'oc.ShortName')
				->orderBy('c.ShortName')
				->get();
	}

	//Day wise report query
    // public function dayWiseQuery($getFromDate, $getToDate, $direction) {
	// 	return DB::connection('sqlsrv5')->table('CallSummary as cm')
	// 			->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),
	// 				DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as TrafficDate"),
	// 				DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
	// 				DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
	// 				DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
	// 			->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
	// 			->where('cm.ReportTrafficDirection','=', $direction)
	// 			->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
	// 			->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
	// 			->get();
	// }


	public function dayWiseQuery($getFromDate, $getToDate, $direction) {
		return DB::connection('sqlsrv5')->table('CallSummary as cm')
				->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),
					DB::raw("{fn CONCAT(LEFT({fn DAYNAME(cm.TrafficDate)}, 3), {fn CONCAT(' ',REPLACE(convert(VARCHAR(30),cm.TrafficDate,106),' ', '-'))})} as TrafficDate"),
					DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall) as ACD"))
				->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=', $direction)
				->groupBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("{fn CONCAT(LEFT({fn DAYNAME(cm.TrafficDate)}, 3), {fn CONCAT(' ',REPLACE(convert(VARCHAR(30),cm.TrafficDate,106),' ', '-'))})}"))
				->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
				->get();
	}

	//Btrac IOS vs Novo
	// public function btracVsNovoDayWiseQuery($getFromDate, $getToDate, $direction, $companyId)
	// {
	// 	return DB::connection('sqlsrv5')->table('CallSummary as cm')
	// 			->select(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"),
	// 				DB::raw("convert(VARCHAR(30),cm.TrafficDate,106) as TrafficDate"),
	// 				DB::raw("SUM(cm.SuccessfulCall) as 'SuccessfulCall'"),
	// 				DB::raw("(SUM(cm.CallDuration)/60) as Duration"))
	// 			->whereBetween('cm.TrafficDate', array($getFromDate, $getToDate))
	// 			->where('cm.ReportTrafficDirection','=', $direction)
	// 			->where('cm.InCompanyID','=', $companyId)
	// 			->groupBy('cm.InCompanyID', DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"), DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
	// 			->orderBy(DB::raw("convert(VARCHAR(30),cm.TrafficDate,112)"))
	// 			->get();
	// }

	public function btracVsNovoDayWiseQuery($getFromDate, $getToDate, $direction)
	{
		return DB::connection('sqlsrv5')
				->select("
					SELECT cy.OrderDate,cy.TrafficDate, btracMin, novoMin FROM (
						SELECT convert(VARCHAR(30),i.TrafficDate,112) as OrderDate, convert(VARCHAR(30),i.TrafficDate,106) as TrafficDate, round(sum(i.CallDuration),2)/60 btracMin
						FROM CallSummary i
						WHERE i.TrafficDate BETWEEN '$getFromDate' And '$getToDate'
						AND i.ReportTrafficDirection=$direction AND i.InCompanyID = 1 GROUP BY convert(VARCHAR(30),i.TrafficDate,112), convert(VARCHAR(30),i.TrafficDate,106)) cy,
						(SELECT convert(VARCHAR(30),o.TrafficDate,112) as OrderDate, convert(VARCHAR(30),o.TrafficDate,106) as TrafficDate, sum(o.CallDuration)/60 novoMin
						FROM CallSummary o
						WHERE o.TrafficDate BETWEEN '$getFromDate' And '$getToDate'
						AND o.ReportTrafficDirection=$direction AND o.InCompanyID = 2 GROUP BY convert(VARCHAR(30),o.TrafficDate,112), convert(VARCHAR(30),o.TrafficDate,106)) cm
					where cy.OrderDate=cm.OrderDate
					ORDER BY cy.OrderDate
				");
	}

	//Hourly query
	public function hourlyQuery($getFromDate, $getToDate, $direction)
	{
		return DB::connection('sqlsrv5')->table('HourlyTraffic as cm')
				->select(DB::raw("substring(convert(VARCHAR, cm.ConnectionTime,120),0,14) as TrafficDate"),
					DB::raw("COUNT(*) as 'SuccessfulCall'"),
					DB::raw("(SUM(cm.CallDuration)/60) as Duration"),
					DB::raw("(SUM(cm.CallDuration)/60)/COUNT(*) as ACD"))
				->whereBetween('cm.ConnectionTime', array($getFromDate, $getToDate))
				->where('cm.ReportTrafficDirection','=', $direction)
				->groupBy(DB::raw("substring(convert(VARCHAR, cm.ConnectionTime,120),0,14)"))
				->orderBy(DB::raw("substring(convert(VARCHAR, cm.ConnectionTime,120),0,14)"))
				->get();
	}


	//Destination query
	public function destinationQuery($getFromDate, $getToDate)
	{
		return DB::connection('sqlsrv5')
				->select("select i.ShortName as 'ansName',o.ShortName as 'igwName',r.DestinationName as 'destinationName',c.InRatedPrefix as 'inRatedPrefix', Sum(c.SuccessfulCall) 'SuccessfulCall', round(sum(c.CallDuration)/60,2) 'Duration',round(sum(c.BillDuration)/60,2) 'BillDuration'
				from CallSummary c ,Company i,Company o,RateListWiseCodeandRate r
				Where c.TrafficDate between '$getFromDate' And '$getToDate'
				AND c.ReportTrafficDirection=2
				AND c.InCompanyID=i.CompanyID
				AND c.OutCompanyID=o.CompanyID
				AND c.InRatedPrefix=r.DestinationCode
				AND r.RateListType=2 AND c.TrafficDate
				BETWEEN i.FromActiveDate
				AND i.ToActiveDate
				AND c.TrafficDate
				BETWEEN o.FromActiveDate AND o.ToActiveDate
				group by i.ShortName,o.ShortName,r.DestinationName,c.InRatedPrefix
				order by 1,2,3,4");
	}

	public function stickySummaryPercent($getFromDate, $getToDate)
	{
		return DB::connection('sqlsrv5')
				->select("SELECT org.ShortName, org.OrgCallPercent, org.OrgDurPercent, ter.TerCallsPercent, ter.TerDurPercent FROM
				(SELECT ic.ShortName,(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall from CallSummary cm
				Where cm.TrafficDate between '$getFromDate' And '$getToDate' and cm.ReportTrafficDirection= 1)) AS  'OrgCallPercent',
				(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration from CallSummary cm
				Where cm.TrafficDate between '$getFromDate' And '$getToDate' and cm.ReportTrafficDirection= 1) AS 'OrgDurPercent'
				from CallSummary cm ,Company ic
				Where cm.TrafficDate between '$getFromDate' And '$getToDate'
				AND cm. ReportTrafficDirection = 1
				AND cm.InCompanyID=ic.CompanyID
				GROUP BY ic.ShortName) AS org,
				(SELECT ic.ShortName,(SUM(cm.SuccessfulCall)/(SELECT SUM(cm.SuccessfulCall) AS scall from CallSummary cm
				Where cm.TrafficDate between '$getFromDate' And '$getToDate' and cm.ReportTrafficDirection= 2)) AS  'TerCallsPercent',
				(SUM(cm.CallDuration)/60)/(SELECT SUM(cm.CallDuration)/60 AS duration from CallSummary cm
				Where cm.TrafficDate between '$getFromDate' And '$getToDate' and cm.ReportTrafficDirection= 2) AS 'TerDurPercent'
				from CallSummary cm ,Company ic
				Where cm.TrafficDate between '$getFromDate' And '$getToDate'
				AND cm. ReportTrafficDirection = 2
				AND cm.OutCompanyID=ic.CompanyID
				GROUP BY ic.ShortName) AS ter
				WHERE org.ShortName = ter.ShortName
				ORDER BY org.ShortName");
	}


	/*
	SELECT substring(convert(VARCHAR, cm.TrafficDate,120),0,14) incp,count(*) sCall,
	SUM(cm.CallDuration) Dur  from HourlyTraffic cm
	Where cm.TrafficDate between '20200119' And '20200119 23:59:59'
	AND cm. ReportTrafficDirection=2
	GROUP BY substring(convert(VARCHAR, cm.TrafficDate,120),0,14)
	ORDER BY


	SELECT convert(VARCHAR(30),cm.TrafficDate,112) incp,count(*) sCall,
	SUM(cm.CallDuration) Dur  from HourlyTraffic cm
	Where cm.TrafficDate between '20200121 00:00:00' And '20200121 23:59:59'
	AND cm. ReportTrafficDirection=2
	GROUP BY convert(VARCHAR(30),cm.TrafficDate,112)
	ORDER BY convert(VARCHAR(30),cm.TrafficDate,112)


	SELECT cm.ReportTrafficDirection dir,substring(convert(VARCHAR, cm.TrafficDate,120),0,14) incp,count(*) sCall,
	SUM(cm.CallDuration) Dur  from HourlyTraffic cm
	Where cm.TrafficDate between {fn CONCAT(convert(VARCHAR, GETDATE()-1,112),' 00:00:00')} And {fn CONCAT(convert(VARCHAR, GETDATE()-1,112),' 23:59:59')}
	AND cm. ReportTrafficDirection=2
	GROUP BY cm.ReportTrafficDirection,substring(convert(VARCHAR, cm.TrafficDate,120),0,14)  ORDER BY 1,2

	//Day and date
	SELECT { fn CONCAT(LEFT({fn DAYNAME(cm.TrafficDate)}, 3),REPLACE(convert(VARCHAR(30),cm.TrafficDate,106),' ', '-')) } AS daydate, count(*) sCall, SUM(cm.CallDuration) Dur
	from CallSummary cm
	Where cm.TrafficDate between '1 Jan 2020 00:00:00' And '20 Jan 2020 23:59:59'
	and cm.ReportTrafficDirection=1
	GROUP BY {fn DAYNAME(cm.TrafficDate)}, convert(VARCHAR(30),cm.TrafficDate,106)
	ORDER BY convert(VARCHAR(30),cm.TrafficDate,106)

	//f-2
	SELECT { fn CONCAT(LEFT({fn DAYNAME(cm.TrafficDate)}, 3), {fn CONCAT(' ',REPLACE(convert(VARCHAR(30),cm.TrafficDate,106),' ', '-'))})} AS daydate, count(*) sCall, SUM(cm.CallDuration) Dur
	from CallSummary cm
	Where cm.TrafficDate between '1 Jan 2020 00:00:00' And '20 Jan 2020 23:59:59'
	and cm.ReportTrafficDirection=1
	GROUP BY {fn DAYNAME(cm.TrafficDate)}, convert(VARCHAR(30),cm.TrafficDate,106)
	ORDER BY convert(VARCHAR(30),cm.TrafficDate,106)
	*/

}
