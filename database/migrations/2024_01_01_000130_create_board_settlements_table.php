<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_settlements', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();

            // ── Inventory ──────────────────────────────────────────────────────
            $table->unsignedInteger('total_tickets_received')->default(0);
            $table->decimal('total_ticket_value', 12, 2)->default(0);

            // ── NLB Winning Tiers (qty of winning tickets per denomination) ────
            $table->unsignedInteger('nlb_40')->default(0);
            $table->unsignedInteger('nlb_80')->default(0);
            $table->unsignedInteger('nlb_120')->default(0);
            $table->unsignedInteger('nlb_160')->default(0);
            $table->unsignedInteger('nlb_200')->default(0);
            $table->unsignedInteger('nlb_240')->default(0);
            $table->unsignedInteger('nlb_500')->default(0);
            $table->unsignedInteger('nlb_1000')->default(0);
            $table->unsignedInteger('nlb_1080')->default(0);
            $table->unsignedInteger('nlb_2000')->default(0);
            $table->unsignedInteger('nlb_4000')->default(0);
            $table->unsignedInteger('nlb_5000')->default(0);
            $table->unsignedInteger('nlb_6000')->default(0);
            $table->unsignedInteger('nlb_15000')->default(0);
            $table->decimal('nlb_total', 12, 2)->default(0);

            // ── DLB Winning Tiers ─────────────────────────────────────────────
            $table->unsignedInteger('dlb_40')->default(0);
            $table->unsignedInteger('dlb_80')->default(0);
            $table->unsignedInteger('dlb_120')->default(0);
            $table->unsignedInteger('dlb_200')->default(0);
            $table->unsignedInteger('dlb_240')->default(0);
            $table->unsignedInteger('dlb_280')->default(0);
            $table->unsignedInteger('dlb_400')->default(0);
            $table->unsignedInteger('dlb_500')->default(0);
            $table->unsignedInteger('dlb_1000')->default(0);
            $table->unsignedInteger('dlb_2000')->default(0);
            $table->unsignedInteger('dlb_4000')->default(0);
            $table->decimal('dlb_total', 12, 2)->default(0);

            // ── Grand Winning ─────────────────────────────────────────────────
            $table->decimal('total_winning', 12, 2)->default(0);

            // ── Cash Counter Denominations ────────────────────────────────────
            $table->unsignedInteger('cash_10')->default(0);
            $table->unsignedInteger('cash_20')->default(0);
            $table->unsignedInteger('cash_50')->default(0);
            $table->unsignedInteger('cash_100')->default(0);
            $table->unsignedInteger('cash_500')->default(0);
            $table->unsignedInteger('cash_1000')->default(0);
            $table->unsignedInteger('cash_2000')->default(0);
            $table->unsignedInteger('cash_5000')->default(0);
            $table->decimal('cash_total', 12, 2)->default(0);

            // ── Payment Summary ───────────────────────────────────────────────
            $table->decimal('bank_deposits', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);   // winning + cash + bank
            $table->decimal('balance', 12, 2)->default(0);      // ticket_value - total_paid

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_settlements');
    }
};
