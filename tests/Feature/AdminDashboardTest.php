<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->globalAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_ban_user_and_user_cannot_login(): void
    {
        $admin = User::factory()->globalAdmin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.ban', $target))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_banned' => true,
            'is_active' => false,
        ]);

        $this->post('/logout');

        $this->post('/login', [
            'email' => $target->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_deactivate_and_reactivate_user(): void
    {
        $admin = User::factory()->globalAdmin()->create();
        $target = User::factory()->create([
            'is_active' => true,
            'is_banned' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $target))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.activate', $target))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => true,
        ]);
    }

    public function test_banned_user_is_forced_out_from_authenticated_pages(): void
    {
        $user = User::factory()->create([
            'is_banned' => true,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
