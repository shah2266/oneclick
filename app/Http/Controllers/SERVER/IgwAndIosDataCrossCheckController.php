<?php

namespace App\Http\Controllers\SERVER;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Query\DataCrossCheckQuery;
use Illuminate\View\View;

class IgwAndIosDataCrossCheckController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        if($request->ajax()) {
            //$table = DataTables::of($main);
            //$table->addIndexColumn();
            //return $table->make(true);
            return response()->json(DataCrossCheckQuery::dataChecking());
        }

        return view('cross-check');
    }

}
