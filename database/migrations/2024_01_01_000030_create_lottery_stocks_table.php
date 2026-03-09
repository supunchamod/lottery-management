<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('agent_id')->constrained('sales_assistants')->cascadeOnDelete();
            $table->foreignId('lottery_id')->constrained('lotteries')->cascadeOnDelete();
            $table->unsignedInteger('qty_issued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_stocks');
    }
};
