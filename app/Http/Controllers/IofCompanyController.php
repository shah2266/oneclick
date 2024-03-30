<?php

namespace App\Http\Controllers;

use App\Models\IofCompany;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class IofCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $company = IofCompany::all();

        $igwCompanies = IofCompany::IgwCompany()->get(); //IGW company list
        $iosCompanies = IofCompany::IosCompany()->get(); //IOS company list
        $icxCompanies = IofCompany::IcxCompany()->get(); //ICX company list
        $ansCompanies = IofCompany::AnsCompany()->get(); //ANS company list

        return view('platform.igwandios.iof.company.index', compact('company','igwCompanies', 'iosCompanies', 'icxCompanies', 'ansCompanies'));

        //return view('platform.igwandios.iof.company.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $company = new IofCompany();
        return view('platform.igwandios.iof.company.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(Request $request)
    {
        IofCompany::create($this->validateRequest());
        return redirect('platform/igwandios/report/iof/company')->with('success', 'Company info added!');
    }

    /**
     * Display the specified resource.
     *
     * @param IofCompany $company
     * @return int
     */
    public function show(IofCompany $company): int
    {
        //
        return 0;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param IofCompany $company
     * @return Application|Factory|View
     */
    public function edit(IofCompany $company)
    {
        return view('platform.igwandios.iof.company.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param IofCompany $company
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, IofCompany $company)
    {
        $company->update($this->validateRequest());
        return redirect('platform/igwandios/report/iof/company')->with('success', 'Company info update!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param IofCompany $company
     * @return int
     */
    public function destroy(IofCompany $company): int
    {
        //
        return 0;
    }


    public function validateRequest() {
        return tap(request()->validate([
            'type'       => 'required|numeric',
            'precedence' => 'required|numeric',
            'systemId'   => 'string|max:100',
            'fullName'   => 'required|string|max:100',
            'shortName'  => 'required|string|max:100',
            'user_id'    => 'required|numeric',
            'status'     => 'required'
        ]), function () {
            if(request()->method() == 'POST'){
                request()->validate([
                    'shortName'  => 'required|string|max:100',
                    'fullName'   => 'required|string|max:100',
                ]);
            }
        });
    }


}
