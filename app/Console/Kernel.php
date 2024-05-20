<?php

namespace App\Console;

use App\Console\Commands\IgwAndIosCommands\ComparisonReportCommand;
use App\Console\Commands\IgwAndIosCommands\IofDailySummaryReportCommand;
use App\Console\Commands\IgwAndIosCommands\IofInOutDayWiseReportCommand;
use App\Console\Commands\IgwAndIosCommands\IosAndIgwClientsReportCommand;
use App\Console\Commands\IgwOperatorSwitchCommands\IosBtrcReportCommand;
use App\Console\Commands\IgwOperatorSwitchCommands\IOSDailyCallSummaryReportCommand;
use App\Console\Commands\IgwOperatorSwitchCommands\GenerateIosBtrcMonthlyReportCommand;
use App\Console\Commands\InternationalGatewayCommands\IgwCallSummaryReportCommand;
use App\Console\Commands\InternationalGatewayCommands\IosDayWiseReportFromIgwCommand;
use App\Console\Commands\InternationalGatewayCommands\OSWiseReportCommand;
use App\Traits\ScheduleProcessing;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    use ScheduleProcessing;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        IOSDailyCallSummaryReportCommand::class,
        IofDailySummaryReportCommand::class,
        ComparisonReportCommand::class,
        IofInOutDayWiseReportCommand::class,
        IosBtrcReportCommand::class,
        IosAndIgwClientsReportCommand::class,
        IosDayWiseReportFromIgwCommand::class,
        OSWiseReportCommand::class,
        IgwCallSummaryReportCommand::class,
        GenerateIosBtrcMonthlyReportCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Process and schedule commands based on schedules defined in the database
        // (env('APP_ENV') !== 'local') ? $this->processSchedules($schedule) : $schedule->command('ios:destination-wise-outgoing-report')->everyMinute();
        //$this->processSchedules($schedule);
        $schedule->command('igw:day-wise-profit-loss-report')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
