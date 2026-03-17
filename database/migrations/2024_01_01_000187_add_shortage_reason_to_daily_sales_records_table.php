<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_records', function (Blueprint $table) {
            // Reason required when balance > 0 (Outstanding)
            $table->enum('shortage_reason', ['credit', 'scam_winning'])
                  ->nullable()
                  ->after('balance')
                  ->comment('Required when balance is Outstanding: credit or scam_winning');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_records', function (Blueprint $table) {
            $table->dropColumn('shortage_reason');
        });
    }
};
