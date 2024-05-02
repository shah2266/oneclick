<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use App\Models\NoclickSchedule;

trait ScheduleProcessing
{
    use CdrFileStatus;

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
        $noclickSchedule = $this->findFrequencyWiseCommand(1); // Frequency: 1, It is monthly value

        foreach ($noclickSchedule as $command) {
            $scheduleDays = explode(',', $command->days);
            foreach ($scheduleDays as $day) {
                //$todayDate = '05-Feb-2024';
                $todayDate = Carbon::now()->format('d-M-Y');

                // Get the date of the first occurrence of the specified day of the current month
                $firstDay = Carbon::now()->firstOfMonth()->next(Carbon::parse(ucfirst($day))->englishDayOfWeek)->addWeeks(2)->format('d-M-Y');

                if($todayDate === $firstDay) {
                    $this->scheduleCommand($command, $schedule);
                }
            }

        }
    }

    /**
     * Check if today is monday. for monthly schedules
     * @return bool
     */
    protected function checkMonthlyScheduleDay(): bool
    {
        // Get today's date
        //$todayDate = '04-Feb-2024';
        $todayDate = Carbon::now()->format('d-M-Y');

        // Get the date of the first Monday of the current month
        $firstMonday = Carbon::now()->firstOfMonth()->next(CarbonInterface::TUESDAY)->addWeeks(2)->format('d-M-Y');

        // Compare today's date with the date of the first Monday of the current month
        // dump($todayDate . ' vs ' . $firstMonday);
        return $todayDate === $firstMonday;
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
