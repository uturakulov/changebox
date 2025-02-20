<?php

namespace Tests\Unit;

use App\Jobs\ProcessBatchJob;
use App\Models\Batch;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\BatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_batches_successfully(): void
    {
        Transaction::factory()->count(3)->create(['status' => 'pending', 'batch_id' => null]);

        Queue::fake();

        Setting::factory()->create([
            'key' => 'batch_size',
            'value' => 500,
        ]);

        BatchService::createBatches();

        $this->assertDatabaseHas('batches', ['status' => 'pending']);

        $batch = Batch::query()->first();

        $this->assertDatabaseHas('transactions', [
            'batch_id' => $batch->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessBatchJob::class, 1);
    }

    public function test_no_batches_created_when_no_pending_transactions(): void
    {
        Transaction::factory()->count(3)->create(['status' => 'completed', 'batch_id' => null]);

        Queue::fake();

        BatchService::createBatches();

        $this->assertDatabaseCount('batches', 0);
        Queue::assertNothingPushed();
    }
}
