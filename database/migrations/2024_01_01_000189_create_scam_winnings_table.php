<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scam_winnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();
            $table->date('date')->comment('Date the scam winning was reported');
            $table->string('ticket_barcode', 120)->comment('Scanned ticket barcode / serial number');
            $table->decimal('reported_winning_value', 12, 2)
                  ->comment('Amount the assistant claimed as winning (inflated / scam amount)');
            $table->decimal('actual_winning_value', 12, 2)->default(0.00)
                  ->comment('Real winning value of the ticket (0 if fully fake)');
            $table->decimal('difference', 12, 2)->default(0.00)
                  ->comment('reported_winning_value - actual_winning_value = amount to be paid back');
            $table->boolean('is_paid_back')->default(false);
            $table->decimal('paid_back_amount', 12, 2)->default(0.00)
                  ->comment('Amount the assistant has already paid back');
            $table->date('paid_back_date')->nullable();
            $table->foreignId('daily_sale_record_id')
                  ->nullable()
                  ->constrained('daily_sales_records')
                  ->nullOnDelete()
                  ->comment('Daily sale record on which this scam was detected');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_winnings');
    }
};
