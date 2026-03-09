<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_transactions', function (Blueprint $table) {
            $table->id();

            // ── Core fields (match image columns) ─────────────────────────────
            $table->date('date');                               // Date 01
            $table->date('date_02')->nullable();                // Date 02 (e.g. delivery date)
            $table->enum('description', [
                'get_tickets',
                'paid_bill',
                'credit',
            ]);

            // ── Get Tickets fields ─────────────────────────────────────────────
            $table->unsignedInteger('ticket_qty')->nullable();  // Amt
            $table->decimal('ticket_value', 12, 2)->default(0); // Value (ticket batch cost)

            // ── Paid Bill fields ───────────────────────────────────────────────
            $table->decimal('winning_amount', 12, 2)->default(0); // Winning column
            $table->decimal('cash_amount',    12, 2)->default(0); // Cash column
            $table->decimal('bank_deposits',  12, 2)->default(0); // Bank deposit

            // Denomination breakdowns stored as JSON for full audit trail
            $table->json('nlb_tiers')->nullable();  // { nlb_40: 5, nlb_80: 2, ... }
            $table->json('dlb_tiers')->nullable();  // { dlb_40: 3, dlb_120: 1, ... }
            $table->json('cash_denoms')->nullable(); // { cash_100: 4, cash_500: 2, ... }

            // ── Credit / adjustment field ──────────────────────────────────────
            $table->decimal('credit_amount', 12, 2)->default(0); // standalone credit

            // ── Computed columns (maintained by recalculateBalances) ───────────
            // cr_amount: net effect on balance for this row
            //   +ticket_value  for get_tickets
            //   +credit_amount for credit
            //   -(winning+cash+bank) for paid_bill
            $table->decimal('cr_amount', 12, 2)->default(0);

            // Running balance (sum of all cr_amounts up to and including this row)
            $table->decimal('balance', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'id']); // ordering index for recalculation
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_transactions');
    }
};
