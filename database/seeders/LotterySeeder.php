<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LotterySeeder extends Seeder
{
    public function run(): void
    {
        $lotteries = [
            ['name' => 'Mahajana Sampatha', 'board' => 'NLB'],
            ['name' => 'Govisetha', 'board' => 'DLB'],
            ['name' => 'Ada Kotipathi', 'board' => 'DLB'],
            ['name' => 'Shanida', 'board' => 'NLB'],
            ['name' => 'Lagana Wasana', 'board' => 'DLB'],
            ['name' => 'Supiri Wasana', 'board' => 'NLB'],
            ['name' => 'Jayoda', 'board' => 'DLB'],
            ['name' => 'Handahana', 'board' => 'NLB'],
            ['name' => 'Kotipathi Kapruka', 'board' => 'DLB'],
            ['name' => 'Dhana Nidhanaya', 'board' => 'NLB'],
        ];

        foreach ($lotteries as $lottery) {
            DB::table('lotteries')->insert([
                'name' => $lottery['name'],
                'board' => $lottery['board'],
                'unit_price' => 40.00, // Samanya milaya
                'commission_rate' => 12.50, // Commission eka
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}