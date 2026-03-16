<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_records', function (Blueprint $table) {
            $table->unsignedInteger('denom_5')->default(0)->after('unit_price');
            $table->unsignedInteger('denom_10')->default(0)->after('denom_5');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_records', function (Blueprint $table) {
            $table->dropColumn(['denom_5', 'denom_10']);
        });
    }
};
