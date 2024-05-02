<?php

namespace App\Traits;

use Carbon\Carbon;

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
}
