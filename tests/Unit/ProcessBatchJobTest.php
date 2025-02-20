<?php

namespace Tests\Unit;

use App\Jobs\ProcessBatchJob;
use App\Models\Balance;
use App\Models\Batch;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProcessBatchJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_batch_processing(): void
    {
        $batch = Batch::factory()->create(['status' => 'pending']);

        $userId = fake()->uuid();
        Balance::factory()->create(['user_id' => $userId, 'currency' => 'USD', 'amount' => 1000]);

        Transaction::factory()->count(2)->create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'currency_from' => 'USD',
            'amount_from' => 100,
            'status' => 'pending',
        ]);

        $job = new ProcessBatchJob($batch);
        $job->handle();

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $userId,
            'currency' => 'USD',
            'amount' => 800, // 1000 - (2 * 100)
        ]);
    }

    public function test_batch_processing_failure_due_to_insufficient_funds(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ошибка в транзакции ID:');

        $batch = Batch::factory()->create(['status' => 'pending']);

        $userId = fake()->uuid();
        Balance::factory()->create(['user_id' => $userId, 'currency' => 'USD', 'amount' => 50]);

        Transaction::factory()->count(2)->create([
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'currency_from' => 'USD',
            'amount_from' => 100,
            'status' => 'pending',
        ]);

        Log::shouldReceive('error')->once();

        $job = new ProcessBatchJob($batch);
        $job->handle();

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'batch_id' => $batch->id,
            'status' => 'failed',
        ]);
    }
}
