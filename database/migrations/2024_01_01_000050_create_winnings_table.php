<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winnings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('board_type', ['NLB', 'DLB']);
            $table->unsignedInteger('cat_40')->default(0)->comment('Count of Rs.40 winning tickets');
            $table->unsignedInteger('cat_80')->default(0)->comment('Count of Rs.80 winning tickets');
            $table->unsignedInteger('cat_100')->default(0)->comment('Count of Rs.100 winning tickets');
            $table->unsignedInteger('cat_500')->default(0)->comment('Count of Rs.500 winning tickets');
            $table->unsignedInteger('cat_1000')->default(0)->comment('Count of Rs.1000 winning tickets');
            $table->decimal('total_val', 12, 2)->default(0.00)->comment('Computed total payout value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winnings');
    }
};
