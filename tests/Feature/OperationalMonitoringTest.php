<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use App\Models\IndicatorCategory;
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

        $this->assertSame(10.0, $scores[$first->indicator_category_id]);
        $this->assertSame(8.0, $scores[$second->indicator_category_id]);

        $this->get(route('admin.monitoring.branch.report', [$branch, 'period' => '2026-08']))
            ->assertOk()
            ->assertSee('Sikap Kerja')
            ->assertSee('Penilaian Driver')
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

    public function test_monitoring_filters_incomplete_data_and_only_shows_reset_for_active_filters(): void
    {
        $admin = User::factory()->create();
        [$completeBranch, $completeDriver] = $this->makeDriver();
        [$incompleteBranch] = $this->makeDriver();
        $completeBranch->update(['name' => 'Unit Monitoring Lengkap']);
        $incompleteBranch->update(['name' => 'Unit Monitoring Belum Lengkap']);

        DriverAttendance::query()->create([
            'branch_id' => $completeBranch->id,
            'driver_id' => $completeDriver->id,
            'entered_by' => $admin->id,
            'period' => '2026-09-01',
            'present_days' => 22,
            'sick_days' => 0,
            'permitted_days' => 0,
            'absent_days' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring.index'))
            ->assertOk()
            ->assertDontSee('assessment-reset-button', false)
            ->assertDontSee('Terapkan');

        $this->get(route('admin.monitoring.index', [
            'period' => '2026-09',
            'status' => 'incomplete',
            'search' => 'Monitoring',
        ]))
            ->assertOk()
            ->assertSee('assessment-reset-button', false)
            ->assertSee($incompleteBranch->name)
            ->assertDontSee($completeBranch->name)
            ->assertSee('name="search" value="Monitoring"', false)
            ->assertSee('onchange="this.form.requestSubmit()"', false);
    }

    public function test_vehicle_monitoring_uses_only_vehicle_questions_and_selected_period(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();
        $vehicle = Vehicle::factory()->for($branch)->create(['police_number' => 'DK 1234 MON']);
        $vehicleQuestion = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_RATING, 'weight' => 100]);
        $driverQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING, 'weight' => 100]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-09-12 10:00:00']);
        $rating->answers()->create(['question_id' => $vehicleQuestion->id, 'answer_value' => [4]]);
        $rating->answers()->create(['question_id' => $driverQuestion->id, 'answer_value' => [1]]);
        Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-12 10:00:00']);

        $this->actingAs($admin)
            ->get(route('admin.monitoring.show', [$branch, 'target' => 'vehicle', 'period' => '2026-09']))
            ->assertOk()
            ->assertSee('DK 1234 MON')
            ->assertSee('80.0')
            ->assertSee('Total Penilaian');

        $report = app(OperationalMonitoringService::class)->reportRows($branch, app(OperationalMonitoringService::class)->period('2026-09'));
        $this->assertSame(80.0, $report['vehicle_rows']->first()['vehicle_score']);
        $this->assertSame(1, $report['vehicle_rows']->first()['rating_count']);
    }

    public function test_monitoring_report_groups_multiple_questions_into_one_indicator_column(): void
    {
        [$branch, $driver] = $this->makeDriver();
        $vehicle = Vehicle::factory()->for($branch)->create();
        $driverIndicator = IndicatorCategory::factory()->create(['name' => 'Pelayanan Driver', 'target_type' => Question::TARGET_DRIVER]);
        $vehicleIndicator = IndicatorCategory::factory()->create(['name' => 'Kebersihan Kendaraan', 'target_type' => Question::TARGET_VEHICLE]);
        $driverQuestions = Question::factory()->count(2)->for($driverIndicator, 'indicatorCategory')->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $vehicleQuestions = Question::factory()->count(2)->for($vehicleIndicator, 'indicatorCategory')->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_RATING]);
        $ignoredQuestion = Question::factory()->for($vehicleIndicator, 'indicatorCategory')->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_YES_NO]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-09-12 10:00:00']);
        $rating->answers()->create(['question_id' => $driverQuestions[0]->id, 'answer_value' => [5]]);
        $rating->answers()->create(['question_id' => $driverQuestions[1]->id, 'answer_value' => [3]]);
        $rating->answers()->create(['question_id' => $vehicleQuestions[0]->id, 'answer_value' => [4]]);
        $rating->answers()->create(['question_id' => $vehicleQuestions[1]->id, 'answer_value' => [2]]);
        $rating->answers()->create(['question_id' => $ignoredQuestion->id, 'answer_value' => [0]]);
        $monitoring = app(OperationalMonitoringService::class);

        $report = $monitoring->reportRows($branch, $monitoring->period('2026-09'));

        $this->assertCount(1, $report['driver_indicators']);
        $this->assertCount(1, $report['vehicle_indicators']);
        $this->assertCount(2, $report['driver_indicators']->first()['questions']);
        $this->assertCount(2, $report['vehicle_indicators']->first()['questions']);
        $this->assertSame(8.0, $report['rows']->first()['report_scores'][$driverIndicator->id]);
        $this->assertSame(6.0, $report['vehicle_rows']->first()['report_scores'][$vehicleIndicator->id]);
    }

    public function test_combined_report_and_individual_exports_are_available(): void
    {
        $admin = User::factory()->create();
        [$branch, $driver] = $this->makeDriver();
        $vehicle = Vehicle::factory()->for($branch)->create(['police_number' => 'DK 9999 PDF']);
        $question = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_RATING]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-09-10 10:00:00']);
        $rating->answers()->create(['question_id' => $question->id, 'answer_value' => [5]]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring.branch.report', [$branch, 'period' => '2026-09']))
            ->assertOk()
            ->assertSee('MONITORING DRIVER &amp; KENDARAAN', false)
            ->assertSee('PENILAIAN DRIVER')
            ->assertSee('PENILAIAN KENDARAAN')
            ->assertSee('monitoring-vehicle-matrix', false)
            ->assertSee('monitoring-indicator-row', false)
            ->assertSee('DK 9999 PDF');

        $this->get(route('admin.monitoring.driver.export', [$branch, $driver, 'period' => '2026-09']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('admin.monitoring.vehicle.export', [$branch, $vehicle, 'period' => '2026-09']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_driver_vehicle_association_comes_from_ratings_in_selected_period(): void
    {
        [$branch, $driver] = $this->makeDriver();
        $currentVehicle = Vehicle::factory()->for($branch)->create();
        $oldVehicle = Vehicle::factory()->for($branch)->create();
        Rating::factory()->for($branch)->for($driver)->for($currentVehicle)->create(['submitted_at' => '2026-09-10 10:00:00']);
        Rating::factory()->for($branch)->for($driver)->for($oldVehicle)->create(['submitted_at' => '2026-08-10 10:00:00']);
        $monitoring = app(OperationalMonitoringService::class);

        $report = $monitoring->driverReport($branch, $driver, $monitoring->period('2026-09'));

        $this->assertSame([$currentVehicle->id], $report['related_vehicles']->pluck('vehicle.id')->all());
    }

    public function test_branch_admin_cannot_access_other_branch_vehicle_monitoring(): void
    {
        [$ownBranch] = $this->makeDriver();
        [$otherBranch] = $this->makeDriver();
        $vehicle = Vehicle::factory()->for($otherBranch)->create();
        $admin = User::factory()->create(['role' => User::ROLE_BRANCH_ADMIN, 'branch_id' => $ownBranch->id]);

        $this->actingAs($admin)->get(route('admin.monitoring.vehicle', [$otherBranch, $vehicle]))->assertForbidden();
        $this->get(route('admin.monitoring.vehicle.export', [$otherBranch, $vehicle]))->assertForbidden();
    }

    private function makeDriver(): array
    {
        $branch = Branch::factory()->create();
        $driver = Driver::factory()->for($branch)->create();

        return [$branch, $driver];
    }
}
