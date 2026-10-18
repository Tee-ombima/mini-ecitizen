<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_forgot_password_form(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200)
            ->assertSee('Forgot password');
    }

    public function test_user_can_submit_the_forgot_password_form(): void
    {
        $user = User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => 'oldpassword123',
        ]);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_invalid_reset_token_returns_an_error_without_crashing(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'oldpassword123',
        ]);

        $response = $this->from('/reset-password/invalid-token?email=' . urlencode($user->email))
            ->post('/reset-password', [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors(['email']);
    }
}
