<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => fake()->uuid(),
            'status' => 'pending',
            'type' => 'deposit',
            'currency_from' => 'USD',
            'amount_from' => 100,
        ];
    }
}
