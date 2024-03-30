<?php

namespace App\Http\Controllers\IGW;

use App\Http\Controllers\Controller;
use App\Traits\SQLQueryServices;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class IGWDayWiseDataCrossCheckController extends Controller
{

    use SQLQueryServices;

    public function index()
    {
        return view('platform.igw.CrossCheck');
    }

    /**
     * Perform cross-checking of data.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function crossCheck(Request $request): RedirectResponse
    {
        // Validation
        $this->validate($request, [
            'direction' => 'required',
            'fromDate'  => 'required|date',
            'toDate'    => 'required|date',
        ]);

        // Input formatting
        $fromDate = Carbon::parse($request->fromDate)->format('Ymd');
        $toDate = Carbon::parse($request->toDate)->format('Ymd');
        $direction = $request->direction;

        $connectionName = 'sqlsrv1';

        // Fetch main and summary data
        $main = $this->fetchData($connectionName, 'CDR_MAIN', $fromDate, $toDate, $direction, 'ConnectionTime');
        $summary = $this->fetchData($connectionName, 'CallSummary', $fromDate, $toDate, $direction, 'TrafficDate');

        // Redirect with data
        return Redirect::to('/platform/igw/report/crosscheck')->with(compact('main', 'summary', 'direction'));
    }
}
