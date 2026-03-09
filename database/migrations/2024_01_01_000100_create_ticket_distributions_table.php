<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_distributions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();
            $table->foreignId('sub_seller_id')
                  ->constrained('sub_sellers')
                  ->cascadeOnDelete();
            $table->foreignId('lottery_id')
                  ->constrained('lotteries')
                  ->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(0);
            $table->timestamps();

            // One record per (date, sub_seller, lottery)
            $table->unique(['date', 'sub_seller_id', 'lottery_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_distributions');
    }
};
