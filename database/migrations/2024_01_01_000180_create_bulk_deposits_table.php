<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assistant_id')
                  ->constrained('sales_assistants')
                  ->cascadeOnDelete();

            $table->date('date_from');
            $table->date('date_to');

            $table->enum('status', ['pending', 'completed'])->default('pending');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_deposits');
    }
};
