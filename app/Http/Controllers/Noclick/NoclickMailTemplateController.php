<?php

namespace App\Http\Controllers\Noclick;

use App\Http\Controllers\Controller;
use App\Models\NoclickMailTemplate;
use App\Http\Requests\StoreNoclickMailTemplateRequest;
use App\Http\Requests\UpdateNoclickMailTemplateRequest;
use App\Traits\MiddlewareTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class NoclickMailTemplateController extends Controller
{
    use MiddlewareTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $templates = NoclickMailTemplate::all();
        return view('noclick.template.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $template = new NoclickMailTemplate();
        $existingTemplates = $this->getBladeMailTemplates();
        return view('noclick.template.create', compact('template', 'existingTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoclickMailTemplateRequest $request
     * @return RedirectResponse
     */
    public function store(StoreNoclickMailTemplateRequest $request): RedirectResponse
    {
        $this->middleware('check.user.type:1,2');
        NoclickMailTemplate::create($this->validateRequest());
        return redirect()->route('templates.index')->with('success', 'Mail template created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param NoclickMailTemplate $template
     * @return Application|Factory|View
     */
    public function show(NoclickMailTemplate $template)
    {
        return view('noclick.template.edit', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param NoclickMailTemplate $template
     * @return Application|Factory|View
     */
    public function edit(NoclickMailTemplate $template)
    {
        $existingTemplates = $this->getBladeMailTemplates();
        return view('noclick.template.edit', compact('template','existingTemplates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoclickMailTemplateRequest $request
     * @param NoclickMailTemplate $template
     * @return RedirectResponse
     */
    public function update(UpdateNoclickMailTemplateRequest $request, NoclickMailTemplate $template): RedirectResponse
    {
        $this->middleware('check.user.type:1,2');
        $template->update($this->validateRequest());
        return redirect()->route('templates.index')->with('success', 'Mail template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NoclickMailTemplate $template
     * @return RedirectResponse
     */
    public function destroy(NoclickMailTemplate $template): RedirectResponse
    {
        $template = NoclickMailTemplate::findOrFail($template->id);
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Mail template deleted successfully.');
    }

    protected function validateRequest()
    {

        return tap(request()->validate([
            'template_name'             => 'required',
            'to_email_addresses'        => 'required',
            'cc_email_addresses'        => 'required',
            'subject'                   => 'required',
            'has_subject_date'          => 'required',
            'greeting'                  => 'required',
            'mail_body_content'         => 'nullable',
            'has_inline_date'           => 'nullable',
            'has_custom_mail_template'  => 'required',
            'signature'                 => 'required',
            'status'                    => 'required',
            'user_id'                   => 'required|numeric',
        ]),function () {

            if(request()->isMethod('POST')) {
                request()->validate([
                    'template_name'     => 'string|max:100|unique:noclick_mail_templates',
                ]);
            }

            // Unique value check for update
            if(request()->isMethod('PATCH')) {
                // Finding values
                $template = request('template');
                $existingTemplate = NoclickMailTemplate::firstWhere('template_name', $template->template_name);

                // strcmp() is case-sensitive function, this function return 0
                if(strcmp(request()->template_name, $existingTemplate['template_name'])) {
                    request()->validate([
                        'template_name'     => 'string|max:100|unique:noclick_mail_templates',
                    ]);
                } else {
                    return false;
                }
            }

        });
    }

    public function getBladeMailTemplates(): array
    {
        $bladeFiles = File::glob(resource_path('views/emails/*.blade.php'));
        $ignoredFiles = ['template_bak', 'test_template', 'partials', 'signature'];

        $filteredFileNames = [];

        foreach ($bladeFiles as $bladeFile) {

            // $bladeFile contains the path to each Blade view file. Remove the '.blade.php' extension from the file name
            $fileName = str_replace('.blade', '', pathinfo($bladeFile, PATHINFO_FILENAME));

            // Check if the file should be ignored
            if (in_array($fileName, $ignoredFiles)) {
                continue;
            }

            // Add the filtered file name to the array
            $filteredFileNames[] = ucfirst($fileName);
        }

        return $filteredFileNames;
    }
}
