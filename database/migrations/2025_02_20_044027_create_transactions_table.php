<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('batch_id')->nullable();
            $table->enum('type', ['deposit', 'withdrawal', 'exchange']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
            $table->decimal('amount_from', 18, 2);
            $table->string('currency_from');
            $table->decimal('amount_to', 18, 2)->nullable();
            $table->string('currency_to')->nullable();
            $table->decimal('rate', 18, 8)->nullable();
            $table->timestamps();

            // Индексы
            $table->index('user_id');
            $table->index('batch_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
