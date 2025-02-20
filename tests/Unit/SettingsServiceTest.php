<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_batch_size_from_db(): void
    {
        Setting::factory()->create([
            'key' => 'batch_size',
            'value' => 500,
        ]);

        Cache::shouldReceive('remember')->andReturn(500);

        $batchSize = SettingsService::getBatchSize();

        $this->assertEquals(500, $batchSize);
    }

    public function test_get_default_batch_size_when_not_set_in_db(): void
    {
        Cache::shouldReceive('remember')->andReturn(1000);

        $batchSize = SettingsService::getBatchSize();

        $this->assertEquals(1000, $batchSize);
    }
}
