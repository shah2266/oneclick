<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;

trait ReportDateHelper
{
    /**
     * Get the date to be used
     *
     * @return string
     */
    public static function getDateToUse(): string
    {
        $input = Carbon::yesterday(); // input date format: 24-Apr-2024; 24 Apr 2024;
        return Carbon::parse($input)->format('Ymd');
    }

    /**
     * @return array
     */
    public function setReportDateRange(): array
    {
        $fromDate = $toDate = self::getDateToUse();

        return [$fromDate, $toDate];
    }

    public function setReportDateWithSubDays(): array
    {
        // Get the date to be used
        $fromDate = Carbon::parse(self::getDateToUse())->subdays(32);
        $toDate = self::getDateToUse();

        return [$fromDate, $toDate];
    }

    public function setReportCurrentDateRange(): array
    {
        // Get the date to be used
        $fromDate = $toDate = Carbon::parse(self::getDateToUse())->addDay();

        return [$fromDate, $toDate];
    }

    /**
     * @return bool
     */
    public function setMonthlyReportDay(): bool
    {
        // Compare today's date with the date of the first English day name of the current month
        return Carbon::now()->format('d-M-Y') === Carbon::now()->firstOfMonth()->next(CarbonInterface::MONDAY)->format('d-M-Y');
    }


    /**
     * @return array
     */
    public function getDatesForCurrentMonth(): array
    {
        if(Carbon::now()->startOfMonth()->format('Ymd') === Carbon::now()->format('Ymd')) {
            return [Carbon::now()->subMonth()->startOfMonth()->format('Ymd'), Carbon::now()->subMonth()->endOfMonth()->format('Ymd')];
        } else {
            return [Carbon::now()->firstOfMonth()->format('Ymd'), Carbon::now()->subDays()->format('Ymd')];
        }
    }

    /**
     * @return array
     */
    public function getDatesForSubMonth(): array
    {
        if(Carbon::now()->startOfMonth()->format('Ymd') === Carbon::now()->format('Ymd')) {
            return [Carbon::now()->subMonth(2)->startOfMonth()->format('Ymd'), Carbon::now()->subMonth(2)->endOfMonth()->format('Ymd')];
        } else {
            return [Carbon::now()->firstOfMonth()->subMonth()->format('Ymd'), Carbon::now()->subMonth()->subDays()->format('Ymd')];
        }

    }


    /**
     * @param $date
     * @param string $format
     * @return string
     */
    public function dateFormat($date, string $format = 'd-M-Y'): string
    {
        return Carbon::parse($date)->format($format);
    }
}
