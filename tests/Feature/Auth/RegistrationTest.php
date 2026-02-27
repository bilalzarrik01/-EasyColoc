<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_first_registered_user_is_promoted_to_global_admin(): void
    {
        $this->post('/register', [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $firstUser = User::query()->where('email', 'first@example.com')->firstOrFail();
        $this->assertTrue($firstUser->is_global_admin);
    }

    public function test_second_registered_user_is_not_global_admin(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'is_global_admin' => true,
        ]);

        $this->post('/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $secondUser = User::query()->where('email', 'second@example.com')->firstOrFail();
        $this->assertFalse($secondUser->is_global_admin);
    }
}
