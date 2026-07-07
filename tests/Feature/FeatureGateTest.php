<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_sub_admin_blocked_until_feature_activated(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $sub = User::factory()->create(['role' => 'sub-admin']);

        $this->actingAs($sub)->get(route('daily-sales.index'))->assertForbidden();

        $perm = Permission::where('key', 'daily-sales')->firstOrFail();
        $sub->permissions()->attach($perm->id, ['is_active' => true]);

        $this->actingAs($sub)->get(route('daily-sales.index'))->assertOk();
    }

    public function test_admin_bypasses_feature_gate(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('daily-sales.index'))->assertOk();
    }

    public function test_sub_admin_cannot_reach_admin_only_user_routes(): void
    {
        $sub = User::factory()->create(['role' => 'sub-admin']);

        $this->actingAs($sub)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_create_sub_admin_with_selected_permissions(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $perm = Permission::where('key', 'expenses')->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Sub',
            'email' => 'newsub@test.com',
            'role' => 'sub-admin',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'permissions' => [$perm->id],
        ])->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'newsub@test.com')->firstOrFail();
        $this->assertTrue($newUser->hasFeature('expenses'));
        $this->assertFalse($newUser->hasFeature('daily-sales'));
    }
}
