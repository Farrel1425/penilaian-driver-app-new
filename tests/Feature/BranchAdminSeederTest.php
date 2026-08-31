<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BranchAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_one_branch_admin_for_each_branch_without_overwriting_existing_passwords(): void
    {
        $activeBranch = Branch::factory()->create([
            'code' => 'DPS-001',
            'email' => 'denpasar@example.com',
            'status' => Branch::STATUS_ACTIVE,
        ]);
        $inactiveBranch = Branch::factory()->create([
            'code' => 'GIA-001',
            'email' => 'gianyar@example.com',
            'status' => Branch::STATUS_INACTIVE,
        ]);

        $this->seed(BranchAdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'denpasar@example.com',
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => $activeBranch->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'gianyar@example.com',
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => $inactiveBranch->id,
            'status' => User::STATUS_INACTIVE,
        ]);

        $admin = User::query()->where('email', 'denpasar@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('GantiPassword!2026', $admin->password));

        $admin->update(['password' => 'PasswordBaru!2026']);
        $this->seed(BranchAdminSeeder::class);

        $this->assertTrue(Hash::check('PasswordBaru!2026', $admin->fresh()->password));
        $this->assertSame(2, User::query()->count());
    }
}