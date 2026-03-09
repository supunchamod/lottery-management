<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin — change the password after first login!
        User::updateOrCreate(
            ['email' => 'admin@wrsoysalotteries.lk'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('Super Admin created:');
        $this->command->line('  Email   : admin@wrsoysalotteries.lk');
        $this->command->line('  Password: Admin@1234');
        $this->command->warn('  ⚠  Change the password after first login!');
    }
}
