		$sqlPart1 = DB::connection('mysql')->table('iofcompanies')
                        ->select('shortName','systemId','precedence')
                        ->where('type','=',2)
                        ->where('status','=', 1)
                        ->get();
        
        //IGW query
        //This Query get data from server pc (CDR data main source)
        $sqlPart2 = DB::connection('sqlsrv2')->table('CallSummary as cm')
                        ->join('Company as c', 'cm.InCompanyID', '=', 'c.CompanyID')
                        ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                                                        DB::raw("(SUM(cm.CallDuration)/60) duration"),
                                                        DB::raw("((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall)) as ACD"))
                        ->whereBetween('cm.TrafficDate', array('30 Jan 2021 00:00:00', '30 Jan 2021 23:59:59'))
                        ->where('cm.ReportTrafficDirection','=',1)
                        ->groupBy('c.CompanyID', DB::raw("convert(VARCHAR(30),cm.TrafficDate,106)"))
                        ->orderBy('c.CompanyID')
                        ->get();
        
        //ANS query
        $sqlPart2 = DB::connection('sqlsrv2')->table('CallSummary as cm')
                    ->join('Company as c', 'cm.ANSID', '=', 'c.CompanyID')
                    ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                        DB::raw("(SUM(cm.CallDuration)/60) duration"),
                        DB::raw("((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall)) as ACD"))
                    ->whereBetween('cm.TrafficDate', array('30 Jan 2021 00:00:00', '30 Jan 2021 23:59:59'))
                    ->where('cm.ReportTrafficDirection','=',1)
                    ->groupBy('c.companyID')
                    ->orderBy('c.companyID')
                    ->get();
        
        
        
        //ICX query
        $sqlPart2 = DB::connection('sqlsrv2')->table('CallSummary as cm')
            ->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
            ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                DB::raw("(SUM(cm.CallDuration)/60) duration"),
                DB::raw("((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall)) as ACD"))
            ->whereBetween('cm.TrafficDate', array('30 Jan 2021 00:00:00', '30 Jan 2021 23:59:59'))
            ->where('cm.ReportTrafficDirection','=',1)
            ->groupBy('c.companyID')
            ->orderBy('c.companyID')
            ->get();
        
        //IGW query
        $sqlPart2 = DB::connection('sqlsrv1')->table('CallSummary as cm')
            ->join('Company as c', 'cm.OutCompanyID', '=', 'c.CompanyID')
            ->select('c.companyID', DB::raw("SUM(cm.SuccessfulCall) 'successfulCall'"),
                DB::raw("(SUM(cm.CallDuration)/60) duration"),
                DB::raw("((SUM(cm.CallDuration)/60)/SUM(cm.SuccessfulCall)) as ACD"))
            ->whereBetween('cm.TrafficDate', array('30 Jan 2021 00:00:00', '30 Jan 2021 23:59:59'))
            ->where('cm.ReportTrafficDirection','=',1)
            ->groupBy('c.companyID')
            ->orderBy('c.companyID')
            ->get();