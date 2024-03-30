<?php

namespace App\Http\Controllers\SERVER;

use App\Http\Controllers\Controller;
use App\Models\ServerList;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServerListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $server = ServerList::all();
        return view('server.servers.index', compact('server'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $info = new ServerList();
        return view('server.servers.create', compact('info'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(Request $request)
    {
        ServerList::create($this->validateRequest());
        return redirect('server/info')->with('success', 'Server info added!');
    }

    /**
     * Display the specified resource.
     *
     * @param ServerList $info
     * @return Application|Factory|View
     */
    public function show(ServerList $info)
    {
        return view('server.servers.edit', compact('info'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ServerList $info
     * @return Application|Factory|View
     */
    public function edit(ServerList $info)
    {
        return view('server.servers.edit', compact('info'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ServerList $info
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, ServerList $info)
    {
        $info->update($this->validateRequest());
        return redirect('server/info')->with('success', 'Server info updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ServerList $info
     * @return RedirectResponse
     */
    public function destroy(ServerList $info): RedirectResponse
    {
        $info = ServerList::findOrFail($info->id);
        $info->delete();

        return redirect()->route('schedules.index')->with('success', 'Server info deleted successfully.');
    }

    protected function validateRequest()
    {
        return tap(request()->validate([
            'machineName'           => 'required|string|max:100',
            'shortName'             => 'nullable|string|max:100',
            'ipAddress'             => 'string|max:100',
            'operatingSystem'       => 'required|string|max:100',
            'manufacturer'          => 'required|string|max:100',
            'model'                 => 'required|string|max:100',
            'bios'                  => 'nullable|string|max:100',
            'processor'             => 'nullable|string|max:100',
            'hdd'                   => 'required|string|max:100',
            'memoryRam'             => 'required|string|max:100',
            'osMemory'              => 'nullable|string|max:100',
            'applicationRunning'    => 'nullable|string|max:100',
            'comment'               => 'nullable|string',
            'user_id'               => 'required|numeric',
            'status'                => 'required'
        ]), function () {
            if(request()->method() == 'POST'){
                request()->validate([
                    'ipAddress'     => 'string|max:100|unique:server_lists',
                ]);
            }
        });
    }


    /**
     * @return BinaryFileResponse
     */
    public function igwDocumentation(): BinaryFileResponse
    {
        $file = public_path(). '/Platform/Documentation/IGW Documentation.docx';
        return response()->download($file);
    }

    /**
     * @return BinaryFileResponse
     */
    public function iosDocumentation(): BinaryFileResponse
    {
        $file = public_path(). '/Platform/Documentation/IOS Documentation.docx';
        return response()->download($file);
    }
}
