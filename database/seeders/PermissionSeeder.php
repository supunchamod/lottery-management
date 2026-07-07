<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('features') as $feature) {
            Permission::updateOrCreate(
                ['key' => $feature['key']],
                [
                    'label'       => $feature['label'],
                    'description' => $feature['description'] ?? null,
                ]
            );
        }

        $this->command->info('Permissions synced from config/features.php.');
    }
}
