<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_admin_can_only_read_its_own_branch_assessments_and_reports(): void
    {
        [$branchA, $ratingA] = $this->ratingForBranch(5);
        [$branchB, $ratingB] = $this->ratingForBranch(1);
        $branchAdmin = User::factory()->create([
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => $branchA->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($branchAdmin)
            ->get(route('admin.dashboard', ['branch_id' => $branchB->id]))
            ->assertOk()
            ->assertSee($ratingA->driver->full_name)
            ->assertDontSee($ratingB->driver->full_name);

        $this->get(route('admin.assessments.index', ['branch_id' => $branchB->id]))
            ->assertOk()
            ->assertSee($ratingA->driver->full_name)
            ->assertDontSee($ratingB->driver->full_name);

        $this->get(route('admin.assessments.show', $ratingA))->assertOk();
        $this->get(route('admin.assessments.show', $ratingB))->assertForbidden();
        $this->get(route('admin.reports.drivers', ['branch_id' => $branchB->id]))->assertOk()->assertSee($ratingA->driver->full_name)->assertDontSee($ratingB->driver->full_name);
    }

    public function test_branch_admin_cannot_access_master_data_or_user_management(): void
    {
        $branch = Branch::factory()->create();
        $branchAdmin = User::factory()->create(['role' => User::ROLE_BRANCH_ADMIN, 'branch_id' => $branch->id]);

        $this->actingAs($branchAdmin);

        $this->get(route('admin.branches.index'))->assertForbidden();
        $this->get(route('admin.drivers.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.activity-logs.index'))->assertForbidden();
    }

    public function test_admin_can_create_branch_admin_with_assigned_branch(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $branch = Branch::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Admin Cabang Denpasar',
            'email' => 'cabang@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => $branch->id,
            'status' => User::STATUS_ACTIVE,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'cabang@example.com', 'role' => User::ROLE_BRANCH_ADMIN, 'branch_id' => $branch->id]);
    }

    private function ratingForBranch(int $score): array
    {
        $branch = Branch::factory()->create();
        $driver = Driver::factory()->for($branch)->create();
        $vehicle = Vehicle::factory()->for($branch)->create();
        $question = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create();
        $rating->answers()->create(['question_id' => $question->id, 'answer_value' => [$score]]);

        return [$branch, $rating->fresh(['driver'])];
    }
}