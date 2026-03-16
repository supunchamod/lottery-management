<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // Seed the four default routes
        DB::table('assistant_routes')->insert([
            ['name' => 'Route A', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Route B', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Route C', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Route D', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_routes');
    }
};
