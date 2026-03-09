<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('assistant_id')->constrained('sales_assistants')->cascadeOnDelete();
            $table->decimal('tickets_issued_val', 12, 2)->comment('Total value of tickets issued');
            $table->unsignedInteger('returns_qty')->default(0)->comment('Number of returned/unsold tickets');
            $table->decimal('winning_val', 12, 2)->default(0.00)->comment('Total winning amount paid out');
            $table->decimal('cash_collected', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00)->comment('Outstanding balance after collection');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sales');
    }
};
