<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Smart Default Quantity — stores the per-assistant, per-day-of-week, per-lottery
     * default quantity so the distribution grid can be pre-filled automatically.
     *
     * day_of_week follows PHP/Carbon convention:
     *   0 = Sunday, 1 = Monday, … 6 = Saturday
     */
    public function up(): void
    {
        Schema::create('default_distributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();

            $table->foreignId('lottery_id')
                  ->constrained('lotteries')
                  ->cascadeOnDelete();

            // 0 = Sunday … 6 = Saturday  (matches PHP date('w') / Carbon->dayOfWeek)
            $table->tinyInteger('day_of_week')->unsigned()->comment('0=Sun,1=Mon,…,6=Sat');

            $table->unsignedInteger('default_qty')->default(0);

            $table->timestamps();

            // One default per (assistant, lottery, weekday) combination
            $table->unique(['assistant_id', 'lottery_id', 'day_of_week'], 'uq_default_dist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_distributions');
    }
};
