<?php

namespace App\Jobs;

use App\Models\Balance;
use App\Models\Batch;
use App\Models\Transaction;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBatchJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Batch $batch) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $transactions = Transaction::query()->where('batch_id', $this->batch->id)->lockForUpdate()->get();

            foreach ($transactions as $transaction) {
                /** @var Transaction $transaction */
                if (!$this->processTransaction($transaction)) {
                    throw new Exception("Ошибка в транзакции ID: {$transaction->id}");
                }
            }

            $this->batch->update(['status' => 'completed']);
        });
    }

    private function processTransaction(Transaction $transaction): bool
    {
        try {
            $balance = Balance::query()
                ->where('user_id', $transaction->user_id)
                ->where('currency', $transaction->currency_from)
                ->lockForUpdate()
                ->first();

            if (!$balance || $balance->amount < $transaction->amount_from) {
                Log::error("Недостаточно средств для транзакции {$transaction->id}");
                return false;
            }

            // Списание
            $balance->decrement('amount', $transaction->amount_from);

            if ($transaction->amount_to && $transaction->currency_to) {
                Balance::query()->updateOrCreate(
                    ['user_id' => $transaction->user_id, 'currency' => $transaction->currency_to],
                    ['amount' => DB::raw("amount + {$transaction->amount_to}")]
                );
            }

            $transaction->update(['status' => 'completed']);
            return true;

        } catch (Exception $e) {
            Log::error("Ошибка при обработке транзакции: {$e->getMessage()}");
            return false;
        }
    }

    public function failed(Exception $e): void
    {
        Log::error("Батч {$this->batch->id} провалился: " . $e->getMessage());

        Transaction::query()
            ->where('batch_id', $this->batch->id)
            ->update(['status' => 'failed']);

        $this->batch->update(['status' => 'failed']);
    }
}
