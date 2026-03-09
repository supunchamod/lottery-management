<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();

            // Ticket issuance
            $table->unsignedInteger('tickets_issued_qty')->default(0);
            $table->decimal('unit_price', 8, 2)->default(0);
            $table->decimal('value', 12, 2)->default(0)->comment('qty × unit_price');

            // Cash counter denominations
            $table->unsignedInteger('denom_20')->default(0);
            $table->unsignedInteger('denom_50')->default(0);
            $table->unsignedInteger('denom_100')->default(0);
            $table->unsignedInteger('denom_500')->default(0);
            $table->unsignedInteger('denom_1000')->default(0);
            $table->unsignedInteger('denom_5000')->default(0);
            $table->decimal('cash', 12, 2)->default(0)->comment('sum of denomination amounts');

            // Winnings
            $table->decimal('nlb_winning', 12, 2)->default(0);
            $table->decimal('dlb_winning', 12, 2)->default(0);
            $table->decimal('total_winning', 12, 2)->default(0)->comment('nlb + dlb');

            // Calculated totals
            $table->decimal('cw', 12, 2)->default(0)->comment('cash + total_winning');
            $table->decimal('balance', 12, 2)->default(0)->comment('value - cw; positive = outstanding');

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['date', 'assistant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sales_records');
    }
};
