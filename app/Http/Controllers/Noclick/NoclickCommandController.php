<?php

namespace App\Http\Controllers\Noclick;

use App\Http\Controllers\Controller;
use App\Models\NoclickCommand;
use App\Http\Requests\StoreNoclickCommandRequest;
use App\Http\Requests\UpdateNoclickCommandRequest;
use App\Models\NoclickMailTemplate;
use App\Traits\MiddlewareTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoclickCommandController extends Controller
{
    use MiddlewareTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $commands = NoclickCommand::all();

        return view('noclick.command.index', compact('commands'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $command = new NoclickCommand();
        $mailTemplates = NoclickMailTemplate::active()->get();

        return view('noclick.command.create', compact('command', 'mailTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoclickCommandRequest $request
     * @return RedirectResponse
     */
    public function store(StoreNoclickCommandRequest $request): RedirectResponse
    {
        NoclickCommand::create($this->validateRequest());
        return redirect()->route('commands.index')->with('success', 'Command created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param NoclickCommand $command
     * @return Application|Factory|View
     */
    public function show(NoclickCommand $command)
    {
        return view('noclick.command.edit', compact('command'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param NoclickCommand $command
     * @return Application|Factory|View
     */
    public function edit(NoclickCommand $command)
    {
        $mailTemplates = NoclickMailTemplate::active()->get();
        return view('noclick.command.edit', compact('command', 'mailTemplates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoclickCommandRequest $request
     * @param NoclickCommand $command
     * @return RedirectResponse
     */
    public function update(UpdateNoclickCommandRequest $request, NoclickCommand $command): RedirectResponse
    {
        $command->update($this->validateRequest());

        return redirect()->route('commands.index')->with('success', 'Command updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NoclickCommand $command
     * @return RedirectResponse
     */
    public function destroy(NoclickCommand $command): RedirectResponse
    {
        $command = NoclickCommand::findOrFail($command->id);
        $command->delete();

        return redirect()->route('commands.index')->with('success', 'Command deleted successfully.');
    }

    protected function validateRequest()
    {

        return tap(request()->validate([
            'name'              => 'required|string|max:100',
            'command'           => 'required',
            'mail_template_id'  => 'required',
            'status'            => 'nullable',
            'user_id'           => 'required|numeric',
        ]),function () {

            if(request()->isMethod('POST')) {
                request()->validate([
                    'command'  => 'string|max:100|unique:noclick_commands',
                ]);
            }

            // Unique value check for update
            if (request()->isMethod('PATCH')) {

                // Retrieve the ID from the route parameters
                $commandId = request()->route('command');

                $existingCommand = NoclickCommand::where('id', '!=', $commandId->id)->first();

                //dump(request('command'). ' vs ' . $existingCommand->command);

                //dd((request('command') === $existingCommand->command));
                if((request('command') === $existingCommand->command)) {
                    request()->validate([
                        'command'  => 'string|max:100|unique:noclick_commands',
                    ]);
                } else {
                    return false;
                }
            }

        });
    }

    public function toggleStatus($id): JsonResponse
    {
        // Find the command by ID
        $command = NoclickCommand::findOrFail($id);

        $command->status = ($command->status === 'on') ? 'off' : 'on';
        $command->save();

        return response()->json(['status' => $command->status]);
    }

    public function getUpdates(): JsonResponse
    {
        // Retrieve all commands with their latest status
        $commands = NoclickCommand::all();

        // Prepare an array to hold command IDs and their corresponding statuses
        $updates = [];

        // Loop through each command and store its ID and status
        foreach ($commands as $command) {
            $updates[$command->id] = $command->status;
        }

        // Return the updates as JSON response
        return response()->json($updates);
    }

}
