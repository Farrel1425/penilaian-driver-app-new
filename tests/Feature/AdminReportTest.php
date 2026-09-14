<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_reports_render_empty_data(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Total Penilaian')->assertSee('Belum ada');
        $this->get(route('admin.monitoring.index'))->assertOk()->assertSee('Belum ada unit kerja');
        $this->get(route('admin.reports.drivers'))->assertOk()->assertSee('Report Driver');
        $this->get(route('admin.reports.vehicles'))->assertOk()->assertSee('Report Kendaraan');
    }

    public function test_report_average_uses_only_rating_answers_not_yes_no(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $driverQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $yesNoQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_YES_NO]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-10 10:00:00']);
        $rating->answers()->create(['question_id' => $driverQuestion->id, 'answer_value' => [5]]);
        $rating->answers()->create(['question_id' => $yesNoQuestion->id, 'answer_value' => [0]]);

        $this->get(route('admin.reports.drivers'))
            ->assertOk()
            ->assertSee('Rating Rata-rata')
            ->assertSee('5')
            ->assertDontSee('2.5');
    }

    public function test_branch_filter_limits_dashboard_data(): void
    {
        $this->actingAs(User::factory()->create());
        [$branchA, $driverA, $vehicleA] = $this->makeEntities();
        [$branchB, $driverB, $vehicleB] = $this->makeEntities();
        $driverA->update(['photo' => 'drivers/dashboard-test.jpg']);
        $question = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $this->makeRating($branchA, $driverA, $vehicleA, $question, 5);
        $this->makeRating($branchB, $driverB, $vehicleB, $question, 1);

        $this->get(route('admin.dashboard', ['branch_id' => $branchA->id]))
            ->assertOk()
            ->assertSee('Total Penilaian')
            ->assertSee($driverA->full_name)
            ->assertSee('storage/drivers/dashboard-test.jpg')
            ->assertDontSee($driverB->full_name);
    }

    public function test_dashboard_cards_link_to_filtered_reports_and_rating_detail(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $question = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $rating = $this->makeRating($branch, $driver, $vehicle, $question, 5);

        $this->get(route('admin.dashboard', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee(route('admin.assessments.index', ['branch_id' => $branch->id]), false)
            ->assertSee(route('admin.reports.drivers', ['branch_id' => $branch->id]), false)
            ->assertSee(route('admin.reports.vehicles', ['branch_id' => $branch->id]), false)
            ->assertSee(route('admin.assessments.show', $rating), false)
            ->assertSee('Buka riwayat penilaian hari ini');
    }

    public function test_date_range_filter_limits_assessment_history(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $question = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $old = $this->makeRating($branch, $driver, $vehicle, $question, 2, '2026-08-01 10:00:00');
        $new = $this->makeRating($branch, $driver, $vehicle, $question, 4, '2026-08-12 10:00:00');

        $this->get(route('admin.assessments.index', ['start_date' => '2026-08-10', 'end_date' => '2026-08-13']))
            ->assertOk()
            ->assertSee($new->submitted_at->format('d M Y'))
            ->assertDontSee($old->submitted_at->format('d M Y'));
    }

    public function test_driver_and_vehicle_report_accuracy(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $driverQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $vehicleQuestion = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_RATING]);
        $r1 = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-11 10:00:00']);
        $r1->answers()->create(['question_id' => $driverQuestion->id, 'answer_value' => [4]]);
        $r1->answers()->create(['question_id' => $vehicleQuestion->id, 'answer_value' => [2]]);
        $r2 = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-12 10:00:00']);
        $r2->answers()->create(['question_id' => $driverQuestion->id, 'answer_value' => [2]]);
        $r2->answers()->create(['question_id' => $vehicleQuestion->id, 'answer_value' => [4]]);

        $this->get(route('admin.reports.drivers'))->assertOk()->assertSee($driver->full_name)->assertSee('3');
        $this->get(route('admin.reports.vehicles'))->assertOk()->assertSee($vehicle->police_number)->assertSee('3');
    }

    public function test_history_recap_and_branch_report_use_actual_ratings(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $ratingQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $commentQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_PARAGRAPH]);
        $rating = $this->makeRating($branch, $driver, $vehicle, $ratingQuestion, 5);
        $rating->answers()->create(['question_id' => $commentQuestion->id, 'answer_text' => 'Pelayanan sangat baik.']);

        $this->get(route('admin.assessments.index', ['search' => $driver->full_name]))
            ->assertOk()
            ->assertSee($driver->full_name)
            ->assertSee('Pelayanan sangat baik.');
        $this->get(route('admin.assessments.show', $rating))->assertOk()->assertSee('Detail Penilaian')->assertSee('Pelayanan sangat baik.');
        $this->get(route('admin.assessments.recap', ['group' => 'branch']))->assertOk()->assertSee($branch->name);
        $this->get(route('admin.reports.branches'))->assertOk()->assertSee('Report Unit Kerja')->assertSee($branch->name);
        $this->get(route('admin.assessments.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_recap_separates_driver_and_vehicle_scores_and_exports_files(): void
    {
        $this->actingAs(User::factory()->create());
        [$branch, $driver, $vehicle] = $this->makeEntities();
        $driverQuestion = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING]);
        $vehicleQuestion = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_RATING]);
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => '2026-08-14 09:00:00']);
        $rating->answers()->create(['question_id' => $driverQuestion->id, 'answer_value' => [5]]);
        $rating->answers()->create(['question_id' => $vehicleQuestion->id, 'answer_value' => [1]]);

        $this->get(route('admin.assessments.recap', ['group' => 'driver']))
            ->assertOk()
            ->assertSee('5')
            ->assertDontSee('1.00');
        $this->get(route('admin.assessments.recap', ['group' => 'vehicle']))
            ->assertOk()
            ->assertSee('1');
        $this->get(route('admin.reports.export', ['type' => 'driver']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->get(route('admin.reports.pdf', ['type' => 'driver']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_system_profile_update_and_activity_log_are_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Profil Sistem');

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'system_name' => 'Sistem Penilaian Driver',
                'support_contact' => 'admin@example.com',
                'copyright_text' => '© 2026. Seluruh hak dilindungi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', ['key' => 'system_name', 'value' => 'Sistem Penilaian Driver']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'module' => 'Pengaturan Sistem', 'action' => 'Edit']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('© 2026. Seluruh hak dilindungi.')
            ->assertSee('Design by')
            ->assertSee('images/maiharta-logo.png', false)
            ->assertSee('https://www.maiharta.com/home', false);

        $this->post(route('logout'));
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('© 2026. Seluruh hak dilindungi.')
            ->assertSee('mailto:admin@example.com', false);

        $this->actingAs($user)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Pengaturan Sistem')
            ->assertSee('Export Excel');

        $this->actingAs($user)
            ->get(route('admin.activity-logs.export', ['user_id' => $user->id]))
            ->assertOk()
              ->assertHeader('content-type', 'text/csv; charset=UTF-8');
      }

      public function test_system_support_contact_is_normalized_to_a_safe_link(): void
      {
          SystemSetting::put('support_contact', 'help@example.com');
          $this->assertSame('mailto:help@example.com', SystemSetting::supportContactUrl());

          SystemSetting::put('support_contact', '0812 3456 7890');
          $this->assertSame('https://wa.me/6281234567890', SystemSetting::supportContactUrl());

          SystemSetting::put('support_contact', 'https://support.example.com');
          $this->assertSame('https://support.example.com', SystemSetting::supportContactUrl());

          SystemSetting::put('support_contact', 'kontak tidak valid');
          $this->assertNull(SystemSetting::supportContactUrl());

          SystemSetting::put('copyright_text', null);
          $this->assertNull(SystemSetting::copyrightText());
      }

    private function makeEntities(): array
    {
        $branch = Branch::factory()->create();

        return [$branch, Driver::factory()->for($branch)->create(), Vehicle::factory()->for($branch)->create()];
    }

    private function makeRating(Branch $branch, Driver $driver, Vehicle $vehicle, Question $question, int $value, string $date = '2026-08-12 10:00:00'): Rating
    {
        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create(['submitted_at' => $date]);
        $rating->answers()->create(['question_id' => $question->id, 'answer_value' => [$value]]);

        return $rating;
    }
}
