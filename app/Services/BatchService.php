<?php

namespace App\Services;

use App\Jobs\ProcessBatchJob;
use App\Models\Batch;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class BatchService
{
    public static function createBatches(): void
    {
        DB::transaction(function () {
            $batchSize = SettingsService::getBatchSize();

            // Получаем транзакции по размеру батча
            $transactions = Transaction::query()
                ->whereNull('batch_id')
                ->where('status', 'pending')
                ->limit($batchSize)
                ->lockForUpdate()
                ->get();

            if ($transactions->isEmpty()) {
                return;
            }

            $batch = Batch::query()->create(['status' => 'pending']);

            // Обновляем транзакции в батче
            Transaction::query()
                ->whereIn('id', $transactions->pluck('id'))
                ->update(['batch_id' => $batch->id]);

            Queue::push(new ProcessBatchJob($batch));
        });
    }
}
