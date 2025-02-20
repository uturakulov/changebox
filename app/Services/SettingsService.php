<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public static function getBatchSize(): int
    {
        return Cache::remember('batch_size', 60, function () {
            return (int) Setting::query()
                ->where('key', 'batch_size')
                ->value('value') ?? 1000;
        });
    }
}
