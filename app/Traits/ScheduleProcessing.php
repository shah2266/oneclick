<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\NoclickSchedule;

trait ScheduleProcessing
{
    use CdrFileStatus, ReportDateHelper;

    /**
     * Process schedules based on current day and holiday status.
     * @return void
     */
    public function processSchedules($schedule)
    {
        // Check if today is a holiday
        $holidayDateMatched = $this->holidayDateMatched();

        $sequences = $this->missingFileSequences();

        if(!empty($sequences)) {
            $this->processCdrStatusSchedules($schedule);
        } else {
            if (!$holidayDateMatched) {
                // Process regular schedules
                $this->processRegularSchedules($schedule);

                // Check if today matches the condition for monthly schedules
                if($this->checkMonthlyScheduleDay()) {
                    dump('in');
                    // Process monthly schedules
                    $this->processMonthlySchedules($schedule);
                }

            } else {
                // Holiday schedules
                $this->processHolidaySchedules($schedule);
            }
        }

        //dd('Test_end');
    }

    /**
     * Schedule the command associated with a schedule at the specified time.
     * @param mixed $schedule
     * @return void
     */
    protected function scheduleCommand($ncs, $schedule)
    {
        $command = $ncs->noclickCommand->command;
        $time = Carbon::parse($ncs->time)->isoFormat('HH:mm');

        $manualDateSet = '01-Jan-2024'; // Use yesterday date
        if($manualDateSet === Carbon::yesterday()->format('d-M-Y')) {
            $schedule->command($command)->everyMinute(); // This is for testing
        }

        // Schedule the command using Laravel scheduler
        $schedule->command($command)->dailyAt($time);
    }

    /**
     * Process holiday schedules and schedule the command.
     * @return void
     */
    protected function processHolidaySchedules($schedule)
    {
        $getHolidaySchedule = NoclickSchedule::getHolidayCommand()->get();
        $todayDate = Carbon::now()->format('d-M-Y');

        foreach ($getHolidaySchedule as $command) {
            $holidays = explode(',', $command->holiday);

            if (in_array($todayDate, $holidays)) {
                $this->scheduleCommand($command, $schedule);
            }
        }
    }

    /**
     * Process regular schedules and schedule the command.
     * @return void
     */
    protected function processRegularSchedules($schedule)
    {
//        $noclickSchedule = NoclickSchedule::activeCommand()->get();
        $noclickSchedule = $this->findFrequencyWiseCommand(0); // Frequency: 0 is daily reports

        $today = Str::lower(Carbon::now()->isoFormat('dddd'));

        foreach ($noclickSchedule as $command) {
            $scheduleDays = explode(',', $command->days);

            if (in_array($today, $scheduleDays)) {
                $this->scheduleCommand($command, $schedule);
            }
        }
    }


    /**
     * Process regular schedules and schedule the command.
     * @return void
     */
    protected function processCdrStatusSchedules($schedule)
    {
//        $noclickSchedule = NoclickSchedule::activeCommand()->get();
        $noclickSchedule = $this->findFrequencyWiseCommand(3); // Frequency: 0 is daily reports

        $today = Str::lower(Carbon::now()->isoFormat('dddd'));

        foreach ($noclickSchedule as $command) {
            $scheduleDays = explode(',', $command->days);

            if (in_array($today, $scheduleDays)) {
                $this->scheduleCommand($command, $schedule);
            }
        }
    }


    /**
     * Process monthly schedules and schedule the command.
     * @return void
     */
    protected function processMonthlySchedules($schedule)
    {
        $noclickSchedule = $this->findFrequencyWiseCommand(1); // Frequency: 1, monthly value

        $todayDate = Carbon::now()->format('d-M-Y');
        $dates = NoclickSchedule::createDate();

        foreach ($noclickSchedule as $key=>$command) {
            if($todayDate === $dates[$key]) {
                $this->scheduleCommand($command, $schedule);
            }
        }
    }

    /**
     * Check if today is monday. for monthly schedules
     * @return bool
     */
    protected function checkMonthlyScheduleDay(): bool
    {
        $getFrequency = NoclickSchedule::frequencyOptions()[1];

        $todayDate = Carbon::now()->format('d-M-Y');
        $dates = NoclickSchedule::createDate();

        if($getFrequency === 'Monthly' && in_array($todayDate, $dates)) {
           return true;
        }

        return false;
    }

    /**
     * Check if today is a holiday.
     * @return bool
     */
    protected function holidayDateMatched(): bool
    {
        $getHolidaySchedule = NoclickSchedule::getHolidayCommand()->get();
        $todayDate = Carbon::now()->format('d-M-Y');

        foreach ($getHolidaySchedule as $schedule) {
            $holidays = explode(',', $schedule->holiday);

            if (in_array($todayDate, $holidays)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $frequency
     * @return mixed
     */
    protected function findFrequencyWiseCommand($frequency)
    {
        return NoclickSchedule::where('status', 'on')
            ->where('frequency', $frequency)
            ->whereHas('noclickCommand', function ($query) {
                $query->active()->where('status', 'on');
            })
            ->get();
    }
}
