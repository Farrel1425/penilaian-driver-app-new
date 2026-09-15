<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_requires_an_active_admin(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));

        $inactive = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ]);

        $this->actingAs($inactive)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_active_admin_can_create_another_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'status' => User::STATUS_ACTIVE,
        ]);

        $admin = User::query()->where('email', 'admin.baru@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.users.show', $admin));
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertSame(User::STATUS_ACTIVE, $admin->status);
        $this->assertTrue(Hash::check('password-baru', $admin->password));
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_cannot_deactivate_or_delete_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin);

        $this->patch(route('admin.users.toggle-status', $admin))->assertRedirect();
        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);

        $this->delete(route('admin.users.destroy', $admin))->assertRedirect();
        $this->assertNotNull($admin->fresh());
    }

    public function test_user_management_pages_render_for_an_active_admin(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $otherAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('status-badge-success', false)
            ->assertSee('user-status-cell', false)
            ->assertSee('user-actions-cell', false)
            ->assertSee('table-row-actions', false);
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.show', $otherAdmin))->assertOk();
        $this->get(route('admin.users.edit', $otherAdmin))->assertOk();
    }
}
