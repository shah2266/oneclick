<?php

namespace App\Http\Controllers\IOS;
use App\Http\Controllers\Controller;

use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IOSDayWiseDataCrossCheckController extends Controller
{

    use SQLQueryServices;

    public function index() {
        return view('platform.ios.CrossCheck');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function crossCheck(Request $request): RedirectResponse
    {
        //Validation check
        $this->validate($request, [
            'direction' => 'required',
            'fromDate'  => 'required',
            'toDate'    => 'required',
        ]);

        //Input Date formatting
        $fromDate = Carbon::parse($request->fromDate)->format('Ymd'); //Input fromDate
        $toDate = Carbon::parse($request->toDate)->format('Ymd'); //Input toDate
        $direction = $request->direction;

        $connectionName = 'sqlsrv2';

        // Fetch main and summary data
        $main = $this->fetchData($connectionName, 'CDR_MAIN', $fromDate, $toDate, $direction, 'ConnectionTime');
        $summary = $this->fetchData($connectionName,  'CallSummary', $fromDate, $toDate, $direction, 'TrafficDate');

        // Redirect with data
        return Redirect::to('/platform/ios/report/crosscheck')->with(compact('main','summary','direction'));
    }
}
