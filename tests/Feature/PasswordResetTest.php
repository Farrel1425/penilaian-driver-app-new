<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_request_a_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create();

        $this->get(route('password.request'))->assertOk();

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, ResetPassword::class);
    }

    public function test_active_admin_can_reset_their_password_with_a_valid_token(): void
    {
        $admin = User::factory()->create();
        $token = Password::createToken($admin);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('kata-sandi-baru', $admin->fresh()->password));
    }

    public function test_inactive_admin_does_not_receive_a_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['status' => User::STATUS_INACTIVE]);

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }
}
