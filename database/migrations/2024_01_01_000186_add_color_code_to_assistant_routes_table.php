<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assistant_routes', function (Blueprint $table) {
            $table->string('color_code', 50)->default('gray')->after('name');
        });

        // Assign default colors to the four seeded routes
        DB::table('assistant_routes')->where('name', 'Route A')->update(['color_code' => 'blue']);
        DB::table('assistant_routes')->where('name', 'Route B')->update(['color_code' => 'green']);
        DB::table('assistant_routes')->where('name', 'Route C')->update(['color_code' => 'yellow']);
        DB::table('assistant_routes')->where('name', 'Route D')->update(['color_code' => 'pink']);
    }

    public function down(): void
    {
        Schema::table('assistant_routes', function (Blueprint $table) {
            $table->dropColumn('color_code');
        });
    }
};
