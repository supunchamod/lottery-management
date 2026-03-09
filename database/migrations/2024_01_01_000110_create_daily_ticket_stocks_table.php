<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_ticket_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();
            $table->foreignId('lottery_id')
                  ->constrained('lotteries')
                  ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['date', 'assistant_id', 'lottery_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_ticket_stocks');
    }
};
