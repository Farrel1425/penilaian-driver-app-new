<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use App\Models\Question;
use App\Models\Rating;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Admin\OperationalMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_and_update_monthly_attendance(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();

        $payload = [
            'period' => '2026-09',
            'present_days' => 19,
            'sick_days' => 2,
            'permitted_days' => 1,
            'absent_days' => 0,
        ];

        $this->actingAs($admin)
            ->post(route('admin.monitoring.attendance.store', [$branch, $driver]), $payload)
            ->assertRedirect();

        $attendance = DriverAttendance::query()->sole();
        $this->assertSame(22, $attendance->totalDays());
        $this->assertSame(95.45, $attendance->score());

        $payload['present_days'] = 20;
        $payload['sick_days'] = 1;
        $this->actingAs($admin)->post(route('admin.monitoring.attendance.store', [$branch, $driver]), $payload);

        $this->assertDatabaseCount('driver_attendances', 1);
        $this->assertDatabaseHas('driver_attendances', ['driver_id' => $driver->id, 'present_days' => 20]);
    }

    public function test_september_attendance_uses_weekdays_and_rejects_excess_days(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();
        $monitoring = app(OperationalMonitoringService::class);

        $this->assertSame(22, $monitoring->workingDays($monitoring->period('2026-09')));

        $this->actingAs($admin)
            ->post(route('admin.monitoring.attendance.store', [$branch, $driver]), [
                'period' => '2026-09',
                'present_days' => 23,
                'sick_days' => 0,
                'permitted_days' => 0,
                'absent_days' => 0,
            ])
            ->assertSessionHasErrors('present_days');

        $this->assertDatabaseCount('driver_attendances', 0);
    }

    public function test_attendance_is_complete_only_after_all_workdays_are_recorded(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();
        $monitoring = app(OperationalMonitoringService::class);
        $period = $monitoring->period('2026-09');

        DriverAttendance::query()->create([
            'branch_id' => $branch->id,
            'driver_id' => $driver->id,
            'entered_by' => $admin->id,
            'period' => $period->toDateString(),
            'present_days' => 10,
            'sick_days' => 0,
            'permitted_days' => 0,
            'absent_days' => 0,
        ]);

        $this->assertFalse($monitoring->driverRows($branch, $period)->first()['is_complete']);

        DriverAttendance::query()->where('driver_id', $driver->id)->update(['present_days' => 22]);

        $this->assertTrue($monitoring->driverRows($branch, $period)->first()['is_complete']);
    }

    public function test_monitoring_calculates_weighted_driver_and_attendance_score(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();
        $vehicle = Vehicle::factory()->for($branch)->create();
        $first = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING, 'weight' => 50, 'sort_order' => 1]);
        $second = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING, 'weight' => 50, 'sort_order' => 2]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-12 10:00:00']);
        $rating->answers()->create(['question_id' => $first->id, 'answer_value' => [5]]);
        $rating->answers()->create(['question_id' => $second->id, 'answer_value' => [4]]);
        DriverAttendance::query()->create([
            'branch_id' => $branch->id,
            'driver_id' => $driver->id,
            'entered_by' => $admin->id,
            'period' => '2026-08-01',
            'present_days' => 20,
            'sick_days' => 2,
            'permitted_days' => 1,
            'absent_days' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring.driver', [$branch, $driver, 'period' => '2026-08']))
            ->assertOk()
            ->assertSee('90.0')
            ->assertSee('91.7')
            ->assertSee('90.2');

        $monitoring = app(OperationalMonitoringService::class);
        $report = $monitoring->reportRows($branch, $monitoring->period('2026-08'));
        $scores = $report['rows']->first()['report_scores'];

        $this->assertSame(10.0, $scores[$first->id]);
        $this->assertSame(8.0, $scores[$second->id]);

        $this->get(route('admin.monitoring.branch.report', [$branch, 'period' => '2026-08']))
            ->assertOk()
            ->assertSee('Sikap Kerja')
            ->assertSee('Kinerja Pelayanan')
            ->assertSee('Kehadiran / Absen')
            ->assertDontSee('Kendaraan Terakhir');
    }

    public function test_branch_admin_only_accesses_own_monitoring_data(): void
    {
        [$ownBranch, $ownDriver] = $this->makeDriver();
        [$otherBranch, $otherDriver] = $this->makeDriver();
        $admin = User::factory()->create([
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring.index'))
            ->assertOk()
            ->assertSee($ownBranch->name)
            ->assertDontSee($otherBranch->name);

        $this->get(route('admin.monitoring.show', $otherBranch))->assertForbidden();
        $this->post(route('admin.monitoring.attendance.store', [$otherBranch, $otherDriver]), [
            'period' => '2026-08',
            'present_days' => 20,
            'sick_days' => 0,
            'permitted_days' => 0,
            'absent_days' => 0,
        ])->assertForbidden();
    }

    public function test_monitoring_report_and_pdf_render(): void
    {
        $admin = User::factory()->create();
        [$branch] = $this->makeDriver();

        $this->actingAs($admin)
            ->get(route('admin.monitoring.branch.report', [$branch, 'period' => '2026-08']))
            ->assertOk()
            ->assertSee('Preview Dokumen')
            ->assertDontSee('FINAL / TERKUNCI');

        $this->get(route('admin.monitoring.branch.export', [$branch, 'period' => '2026-08']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function makeDriver(): array
    {
        $branch = Branch::factory()->create();
        $driver = Driver::factory()->for($branch)->create();

        return [$branch, $driver];
    }
}
