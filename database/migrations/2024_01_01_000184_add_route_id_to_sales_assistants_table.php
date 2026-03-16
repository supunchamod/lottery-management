<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_assistants', function (Blueprint $table) {
            $table->foreignId('route_id')
                  ->nullable()
                  ->after('address')
                  ->constrained('assistant_routes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_assistants', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\AssistantRoute::class);
            $table->dropColumn('route_id');
        });
    }
};
