<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Database\Seeders\RecruitmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_open_vacancies_for_all_active_branches_and_can_be_run_repeatedly(): void
    {
        $activeBranches = Branch::factory()->count(2)->create();
        $inactiveBranch = Branch::factory()->create(['status' => Branch::STATUS_INACTIVE]);

        $this->seed(RecruitmentSeeder::class);
        $this->seed(RecruitmentSeeder::class);

        $period = RecruitmentPeriod::query()->sole();

        $this->assertTrue($period->is_active);
        $this->assertDatabaseCount('job_vacancies', 6);
        $this->assertDatabaseCount('job_vacancy_branch', 12);
        $this->assertSame(6, JobVacancy::query()->where('status', JobVacancy::STATUS_OPEN)->count());

        JobVacancy::query()->each(function (JobVacancy $vacancy) use ($activeBranches, $inactiveBranch): void {
            $this->assertEqualsCanonicalizing(
                $activeBranches->modelKeys(),
                $vacancy->branches()->pluck('branches.id')->all(),
            );
            $this->assertFalse($vacancy->branches()->whereKey($inactiveBranch->id)->exists());
        });
    }

    public function test_it_skips_recruitment_data_when_there_are_no_active_branches(): void
    {
        Branch::factory()->create(['status' => Branch::STATUS_INACTIVE]);

        $this->seed(RecruitmentSeeder::class);

        $this->assertDatabaseEmpty('recruitment_periods');
        $this->assertDatabaseEmpty('job_vacancies');
    }
}
