<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistant_id')->constrained('sales_assistants')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('running_balance', 12, 2)->comment('Balance after this transaction');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
