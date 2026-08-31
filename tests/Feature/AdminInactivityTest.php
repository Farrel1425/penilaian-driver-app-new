<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInactivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_logged_out_after_thirty_minutes_without_activity(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($user)
            ->withSession(['admin_last_activity_at' => now()->subMinutes(31)->timestamp])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Sesi Anda berakhir setelah 30 menit tidak ada aktivitas. Silakan login kembali.');

        $this->assertGuest();
    }

    public function test_activity_refreshes_the_admin_inactivity_timer(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($user)
            ->withSession(['admin_last_activity_at' => now()->subMinutes(29)->timestamp])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSessionHas('admin_last_activity_at');
    }
}