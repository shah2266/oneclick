<?php

namespace App\Http\Controllers\Noclick;

use App\Http\Controllers\Controller;
use App\Models\NoclickCommand;
use App\Models\NoclickSchedule;
use App\Http\Requests\StoreNoclickScheduleRequest;
use App\Http\Requests\UpdateNoclickScheduleRequest;
use App\Traits\MiddlewareTrait;
use App\Traits\ScheduleProcessing;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NoclickScheduleController extends Controller
{
    use MiddlewareTrait, ScheduleProcessing;

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function dashboard()
    {
        $frequencyCounts = NoclickSchedule::select('frequency', DB::raw('COUNT(*) as count'))->groupBy('frequency')->get();
        $schedules = NoclickSchedule::count();

        return view('noclick.schedule.dashboard', compact('schedules', 'frequencyCounts'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $schedules = NoclickSchedule::all();
        return view('noclick.schedule.index', compact('schedules'));
    }

    public function getFrequencyWiseSchedule($type)
    {
        $schedules = NoclickSchedule::where('frequency', $type)->get();
        return view('noclick.schedule.show', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $schedule = new NoclickSchedule();
        $commands = NoclickCommand::active()->get();
        $dayNames = $this->dayNames();

        return view('noclick.schedule.create', compact('schedule', 'commands', 'dayNames'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoclickScheduleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreNoclickScheduleRequest $request): RedirectResponse
    {
        //dd(request()->all());
        $data = $this->validateRequest();
        // Convert the array data in "days" field to a string
        if(isset($request->days)) {
            $data['days'] = implode(',', $request->days);
        }

        NoclickSchedule::create($data);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param NoclickSchedule $schedule
     * @return Application|Factory|View
     */
    public function show(NoclickSchedule $schedule)
    {
        return view('noclick.schedule.edit', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param NoclickSchedule $schedule
     * @return Application|Factory|View
     */
    public function edit(NoclickSchedule $schedule)
    {
        //dd(explode(', ', $schedule->days));
        $commands = NoclickCommand::active()->get();
        $dayNames = $this->dayNames();
        return view('noclick.schedule.edit', compact('schedule', 'commands', 'dayNames'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoclickScheduleRequest $request
     * @param NoclickSchedule $schedule
     * @return RedirectResponse
     */
    public function update(UpdateNoclickScheduleRequest $request, NoclickSchedule $schedule): RedirectResponse
    {
        $data = $this->validateRequest();

        if(isset($request->days)) {
            $data['days'] = implode(',', $request->days);
        }

        $schedule->update($data);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NoclickSchedule $schedule
     * @return RedirectResponse
     */
    public function destroy(NoclickSchedule $schedule): RedirectResponse
    {
        $schedule = NoclickSchedule::findOrFail($schedule->id);
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }

    protected function validateRequest()
    {
        return tap(request()->validate([
            'frequency'         => 'required|numeric',
            'command_id'        => 'required',
            'days'              => 'nullable',
            'time'              => 'required',
            'holiday'           => 'nullable',
            'status'            => 'nullable',
            'user_id'           => 'required|numeric',
        ]),function () {

            if(request()->isMethod('POST')) {
                request()->validate([
                    'frequency'  => 'numeric'
                ]);
            }
        });
    }

    protected function dayNames(): array
    {
        // Get the current Carbon instance set to Friday
        $carbon = Carbon::now()->next(CarbonInterface::FRIDAY);

        // Get an array of localized day names
        $dayNames = [];

        // Loop through each of the next 7 days
        for ($i = 0; $i < 7; $i++) {
            // Use isoFormat() with the 'dddd' format string for the full day name
            $dayNames[] = str::lower($carbon->isoFormat('dddd'));
            // Move to the next day
            $carbon->addDay();
        }

        return $dayNames;
    }

    public function toggleStatus($id): JsonResponse
    {
        // Find the schedule by ID
        $schedule = NoclickSchedule::findOrFail($id);

        $schedule->status = ($schedule->status === 'on') ? 'off' : 'on';
        $schedule->save();

        return response()->json(['status' => $schedule->status]);
    }

    public function getUpdates(): JsonResponse
    {
        // Retrieve all schedule with their latest status
        $schedules = NoclickSchedule::all();

        // Prepare an array to hold schedule IDs and their corresponding statuses
        $updates = [];

        // Loop through each schedule and store its ID and status
        foreach ($schedules as $schedule) {
            $updates[$schedule->id] = $schedule->status;
        }

        // Return the updates as JSON response
        return response()->json($updates);
    }

    public function setHoliday(Request $request): JsonResponse
    {
        // Update all records with the selected date where frequency is 2
        NoclickSchedule::where('frequency', '=', 2)->update([
            'holiday' => $request->holiday,
        ]);

        return response()->json(['message' => 'Holiday successfully set.']);
    }
}
