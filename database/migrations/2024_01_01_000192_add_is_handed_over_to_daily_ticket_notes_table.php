<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_ticket_notes', function (Blueprint $table) {
            $table->boolean('is_handed_over')->default(false)->after('is_no_sales');
        });
    }

    public function down(): void
    {
        Schema::table('daily_ticket_notes', function (Blueprint $table) {
            $table->dropColumn('is_handed_over');
        });
    }
};
