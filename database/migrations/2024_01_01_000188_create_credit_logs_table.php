<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();
            $table->date('date')->comment('Date the credit shortage occurred');
            $table->decimal('amount', 12, 2)->comment('Outstanding credit amount owed by assistant');
            $table->foreignId('daily_sale_record_id')
                  ->nullable()
                  ->constrained('daily_sales_records')
                  ->nullOnDelete()
                  ->comment('Linked daily sale record, if any');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->decimal('paid_amount', 12, 2)->default(0.00)->comment('Amount assistant has paid back so far');
            $table->date('paid_at')->nullable()->comment('Date the credit was fully paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_logs');
    }
};
