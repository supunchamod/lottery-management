<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('assistant_id')->constrained('sales_assistants')->cascadeOnDelete();
            $table->boolean('is_no_sales')->default(false);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['date', 'assistant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_ticket_notes');
    }
};
