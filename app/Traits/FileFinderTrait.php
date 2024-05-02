<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait FileFinderTrait {

    use ReportDateHelper;

    public function findFilesByNeedle(array $fileArray, string $format = 'd-M-Y', string $needle = null ): array
    {
        $date = Carbon::parse($needle ?? self::getDateToUse())->format($format);
        return array_filter($fileArray, function ($fileName) use ($date) {
            return Str::contains($fileName, $date);
        });
    }
}
