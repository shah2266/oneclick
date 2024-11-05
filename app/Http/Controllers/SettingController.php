<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        //$app = Setting::where('status', 1)->first();
        $settings = Setting::all();
        return view('setting.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $setting = new Setting();
        return view('setting.create', compact('setting'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSettingRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        //Check if status is being set to 'active'
        if((string) $data['status'] === 'active') {
            // Set all other status 'inactive'
            Setting::where('status', 'active')->update(['status' => 'inactive']);
        }

        if(File::isFile($request->logo)){
            $data['logo'] = $this->fileUpload(request());
        }

        Setting::create($data);

        return redirect()->route('apps.index')->with('success', 'App setting created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Setting $setting
     * @return Response
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Setting $setting
     * @return Application|Factory|View
     */
    public function edit(Setting $setting)
    {
        return view('setting.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSettingRequest $request
     * @param Setting $setting
     * @return RedirectResponse
     */
    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $data = $request->validated();

        // Check if it's the only active one
        if((string) $data['status'] === 'inactive') {
            $activeCount = Setting::where('status', 'active')->count();
            // Prevent deactivating the last active company
            if($setting->status === 'Active' && $activeCount <= 1) {
                return redirect()->back()->with('danger', 'At least one setting must remain active.');
            }
        }

        //Check if status is being set to 'active'
        if((string) $data['status'] === 'active') {
            // Set all other status 'inactive' and ignore request id.
            Setting::where('status', 'active')->where('id', '!=', $setting->id)->update(['status' => 'inactive']);
        }

        if(File::isFile($request->logo)){
            $this->deletePreviousImage($setting->id);
            $data['logo'] = $this->fileUpload(request());
        }

        $setting->update($data);

        return redirect()->route('apps.index')->with('success', 'App setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Setting $setting
     * @return RedirectResponse
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        if($setting->status == 'Inactive'){
            $this->deletePreviousImage($setting->id);
            $setting->delete();
            return redirect()->route('apps.index')->with('success', 'App setting deleted successfully.');
        }

        return redirect()->route('apps.index')->with('danger', 'Cannot delete an active app setting.');
    }

    //Unlink existing image
    protected function deletePreviousImage($id): bool
    {
        $getLogo = Setting::where('id', $id)->first();
        $logoPath = public_path('/assets/images/logo/' . $getLogo->logo);

        if(File::exists($logoPath)) {
            File::delete($logoPath);
        }
        return true;
    }

    protected function fileUpload($request): string
    {

        if(File::isFile($request->logo)) {
            $image	                = $request->file('logo');
            $ext                    = $image->getClientOriginalExtension();
            $this->imageUniqueName  = Str::random(40).'.'.$ext;
            $uploadPath 	        = public_path().'/assets/images/logo/'.$this->imageUniqueName;
            //Uploaded image resize
            Image::make($image)->resize(120,60)->save($uploadPath);
        }

        return $this->imageUniqueName;
    }
}
