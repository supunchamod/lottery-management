<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotteries', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('lotteries', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->default(0)
                  ->comment('Commission percentage (e.g. 12.50 for 12.5%)')
                  ->after('unit_price');
        });
    }
};
