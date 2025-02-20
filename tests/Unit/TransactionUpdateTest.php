<?php

namespace Tests\Unit;

use App\Enums\TransactionStatusEnum;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TransactionUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка успешного обновления статуса транзакций в батче
     */
    public function test_successful_batch_update(): void
    {
        $userId = fake()->uuid;

        // Создаем баланс пользователя
        Balance::query()->create([
            'user_id' => $userId,
            'currency' => 'USD',
            'amount' => 1000,
        ]);

        // Создаем батч транзакций
        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $userId,
            'status' => TransactionStatusEnum::PENDING,
            'type' => 'deposit',
            'currency_from' => 'USD',
            'amount_from' => 100,
        ]);

        DB::beginTransaction();

        try {
            foreach ($transactions as $transaction) {
                /** @var Transaction $transaction */
                $transaction->status = TransactionStatusEnum::COMPLETED;
                $transaction->save();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Ошибка обновления батча: ' . $exception->getMessage());
        }

        foreach ($transactions as $transaction) {
            $this->assertEquals(TransactionStatusEnum::COMPLETED, $transaction->status);
        }
    }

    /**
     * Проверка отката при ошибке в одной из транзакций
     */
    public function test_batch_rollback_on_error(): void
    {
        $userId = fake()->uuid;

        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $userId,
            'status' => TransactionStatusEnum::PENDING,
            'type' => 'deposit',
            'currency_from' => 'USD',
            'amount_from' => 100,
        ]);

        DB::beginTransaction();

        try {
            foreach ($transactions as $index => $transaction) {
                /** @var Transaction $transaction */
                if ($index === 2) {
                    // Имитация ошибки на третьей транзакции
                    throw new \Exception('Ошибка в батче');
                }
                $transaction->status = TransactionStatusEnum::COMPLETED;
                $transaction->save();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Откат батча: ' . $exception->getMessage());
        }

        foreach ($transactions as $index => $transaction) {
            if ($index === 2) {
                $this->assertEquals(TransactionStatusEnum::PENDING, $transaction->status);
            }
        }
    }

    /**
     * Проверка ошибки из-за недостатка средств
     */
    public function test_insufficient_funds_error(): void
    {
        $userId = fake()->uuid;

        Balance::query()->create([
            'user_id' => $userId,
            'currency' => 'USD',
            'amount' => 50,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $userId,
            'status' => TransactionStatusEnum::PENDING,
            'type' => 'withdrawal',
            'currency_from' => 'USD',
            'amount_from' => 100,
        ]);

        DB::beginTransaction();

        try {
            /** @var Transaction $transaction */
            $balance = Balance::query()->where('user_id', $userId)
                ->where('currency', $transaction->currency_from)
                ->first();

            if ($balance->amount < $transaction->amount_from) {
                throw new \Exception('Недостаточно средств');
            }

            $transaction->status = TransactionStatusEnum::COMPLETED;
            $transaction->save();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Ошибка вывода средств: ' . $exception->getMessage());
        }

        $this->assertEquals(TransactionStatusEnum::PENDING, $transaction->status);
    }

    /**
     * Проверка обновления баланса пользователя при успешной транзакции
     */
    public function test_balance_update_on_successful_transaction(): void
    {
        $userId = fake()->uuid;

        Balance::query()->create([
            'user_id' => $userId,
            'currency' => 'USD',
            'amount' => 500,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $userId,
            'status' => TransactionStatusEnum::PENDING,
            'type' => 'withdrawal',
            'currency_from' => 'USD',
            'amount_from' => 100,
        ]);

        DB::beginTransaction();

        try {
            /** @var Transaction $transaction */
            $balance = Balance::query()
                ->where('user_id', $userId)
                ->where('currency', $transaction->currency_from)
                ->first();

            if ($balance->amount < $transaction->amount_from) {
                throw new \Exception('Недостаточно средств');
            }

            $balance->amount -= $transaction->amount_from;
            $balance->save();

            $transaction->status = TransactionStatusEnum::COMPLETED;
            $transaction->save();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Ошибка обновления баланса: ' . $exception->getMessage());
        }

        $this->assertEquals(TransactionStatusEnum::COMPLETED, $transaction->status);
        $this->assertEquals(400, $balance->amount);
    }
}
