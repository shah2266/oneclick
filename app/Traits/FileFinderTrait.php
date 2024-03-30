<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait FileFinderTrait {
    public function findFilesByNeedle(array $fileArray, string $format = 'd-M-Y', string $needle = null ): array
    {
        $date = $needle ?? Carbon::yesterday()->format($format);
        return array_filter($fileArray, function ($fileName) use ($date) {
            return Str::contains($fileName, $date);
        });
    }
}
