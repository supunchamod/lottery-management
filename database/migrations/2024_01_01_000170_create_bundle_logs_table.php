<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_logs', function (Blueprint $table) {
            $table->id();
            $table->date('session_date');
            $table->unsignedInteger('total_bundles')->default(0);
            $table->unsignedInteger('total_tickets')->default(0);
            $table->json('scanned_barcodes')->nullable();   // array of barcode strings
            $table->text('notes')->nullable();
            $table->foreignId('saved_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_logs');
    }
};
