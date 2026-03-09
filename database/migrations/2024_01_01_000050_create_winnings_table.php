<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winnings', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('One winning record per day covers both NLB + DLB');

            // ── NLB prize tiers (ticket count per denomination) ──────────────
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
            $table->decimal('nlb_total', 12, 2)->default(0.00)
                ->comment('Auto-computed NLB payout (populated by WinningService)');

            // ── DLB prize tiers ───────────────────────────────────────────────
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
            $table->decimal('dlb_total', 12, 2)->default(0.00)
                ->comment('Auto-computed DLB payout');

            // ── Grand total ───────────────────────────────────────────────────
            $table->decimal('total_val', 12, 2)->default(0.00)
                ->comment('nlb_total + dlb_total');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winnings');
    }
};
