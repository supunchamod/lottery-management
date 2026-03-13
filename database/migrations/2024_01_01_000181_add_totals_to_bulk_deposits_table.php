<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_deposits', function (Blueprint $table) {
            // Summary totals captured at entry time
            $table->unsignedInteger('total_qty')->default(0)->after('date_to');
            $table->decimal('unit_price', 8, 2)->default(0)->after('total_qty');
            $table->decimal('total_value', 12, 2)->default(0)->after('unit_price');

            // Cash denomination breakdown
            $table->unsignedInteger('denom_5')->default(0)->after('total_value');
            $table->unsignedInteger('denom_10')->default(0)->after('denom_5');
            $table->unsignedInteger('denom_20')->default(0)->after('denom_10');
            $table->unsignedInteger('denom_50')->default(0)->after('denom_20');
            $table->unsignedInteger('denom_100')->default(0)->after('denom_50');
            $table->unsignedInteger('denom_500')->default(0)->after('denom_100');
            $table->unsignedInteger('denom_1000')->default(0)->after('denom_500');
            $table->unsignedInteger('denom_5000')->default(0)->after('denom_1000');
            $table->decimal('total_cash', 12, 2)->default(0)->after('denom_5000');

            // Winnings
            $table->decimal('nlb_winning', 12, 2)->default(0)->after('total_cash');
            $table->decimal('dlb_winning', 12, 2)->default(0)->after('nlb_winning');
            $table->decimal('tw_winning', 12, 2)->default(0)->after('dlb_winning');
            $table->decimal('total_winning', 12, 2)->default(0)->after('tw_winning');

            // Computed aggregates
            $table->decimal('total_cw', 12, 2)->default(0)->comment('total_cash + total_winning')->after('total_winning');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_deposits', function (Blueprint $table) {
            $table->dropColumn([
                'total_qty', 'unit_price', 'total_value',
                'denom_5', 'denom_10', 'denom_20', 'denom_50', 'denom_100', 'denom_500', 'denom_1000', 'denom_5000',
                'total_cash',
                'nlb_winning', 'dlb_winning', 'tw_winning', 'total_winning',
                'total_cw',
            ]);
        });
    }
};
