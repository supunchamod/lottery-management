<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends board_transactions to:
 *  - link back to the board_settlement that generated the row (nullable — manual entries have no settlement)
 *  - store NLB and DLB winning as separate numeric columns for ledger display
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_transactions', function (Blueprint $table) {
            // FK to board_settlements (nullable — not every row comes from a settlement)
            $table->unsignedBigInteger('board_settlement_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('board_settlement_id')
                  ->references('id')
                  ->on('board_settlements')
                  ->onDelete('set null');

            // Split winning into NLB and DLB for individual display columns
            $table->decimal('nlb_winning', 12, 2)->default(0)->after('winning_amount');
            $table->decimal('dlb_winning', 12, 2)->default(0)->after('nlb_winning');
        });
    }

    public function down(): void
    {
        Schema::table('board_transactions', function (Blueprint $table) {
            $table->dropForeign(['board_settlement_id']);
            $table->dropColumn(['board_settlement_id', 'nlb_winning', 'dlb_winning']);
        });
    }
};
