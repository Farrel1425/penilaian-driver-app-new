<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_complete_flow_from_qr_to_success(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        $driver = Driver::factory()->for($branch)->create(['status' => Driver::STATUS_ACTIVE]);
        $driverRating = Question::factory()->create(['target_type' => Question::TARGET_DRIVER, 'answer_type' => Question::TYPE_RATING, 'sort_order' => 1, 'status' => Question::STATUS_ACTIVE]);
        $yesNo = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_YES_NO, 'sort_order' => 2, 'status' => Question::STATUS_ACTIVE]);
        $choice = Question::factory()->create(['target_type' => Question::TARGET_VEHICLE, 'answer_type' => Question::TYPE_MULTIPLE_CHOICE, 'sort_order' => 3, 'status' => Question::STATUS_ACTIVE]);
        $option = $choice->options()->create(['option_text' => 'AC', 'sort_order' => 1]);
        $this->get(route('passenger.rating.entry', $vehicle->qr_token))
            ->assertOk()
            ->assertSee($vehicle->police_number)
            ->assertSee('Scan Ulang QR');
        $this->get(route('passenger.rating.drivers', $vehicle->qr_token))->assertOk()->assertSee($driver->full_name);
        $this->get(route('passenger.rating.driver', [$vehicle->qr_token, $driver]))->assertOk()->assertSee($driver->full_name);
        $this->get(route('passenger.rating.assessor', [$vehicle->qr_token, $driver]))->assertOk()->assertSee('Data Penumpang');
        $this->post(route('passenger.rating.assessor.store', [$vehicle->qr_token, $driver]), ['passenger_name' => 'Made Penilai', 'passenger_unit' => 'Kantor Pusat'])
            ->assertRedirect(route('passenger.rating.assessment', [$vehicle->qr_token, $driver]));
        $this->get(route('passenger.rating.assessment', [$vehicle->qr_token, $driver]))->assertOk()->assertSee($driverRating->question)->assertSee($yesNo->question);

        $payload = [
            'passenger_name' => 'Made Penilai',
            'passenger_unit' => 'Kantor Pusat',
            'submission_token' => $this->app['session.store']->get("passenger_submission_token_{$vehicle->id}_{$driver->id}"),
            'answers' => [
                $driverRating->id => '5',
                $yesNo->id => '1',
                $choice->id => (string) $option->id,
            ],
        ];
        $response = $this->post(route('passenger.rating.submit', [$vehicle->qr_token, $driver]), $payload);

        $rating = Rating::query()->firstOrFail();
        $response->assertRedirect(route('passenger.rating.success', [$vehicle->qr_token, $rating]));
        $this->post(route('passenger.rating.submit', [$vehicle->qr_token, $driver]), $payload)
            ->assertRedirect(route('passenger.rating.success', [$vehicle->qr_token, $rating]));
        $this->assertSame(1, Rating::query()->count());
        $this->assertSame($branch->id, $rating->branch_id);
        $this->assertSame($vehicle->id, $rating->vehicle_id);
        $this->assertSame($driver->id, $rating->driver_id);
        $this->assertSame('Made Penilai', $rating->passenger_name);
        $this->assertSame('Kantor Pusat', $rating->passenger_unit);
        $this->assertCount(3, $rating->answers);
        $this->get(route('passenger.rating.success', [$vehicle->qr_token, $rating]))->assertOk()->assertSee('Terima Kasih');
    }

    public function test_passenger_header_uses_system_support_contact(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        SystemSetting::put('support_contact', '0812 3456 7890');

        $this->get(route('passenger.rating.entry', $vehicle->qr_token))
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('Hubungi bantuan melalui 0812 3456 7890');
    }

    public function test_driver_selection_only_shows_active_drivers_from_vehicle_branch(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        $activeSameBranch = Driver::factory()->for($branch)->create(['full_name' => 'Driver Satu Cabang', 'status' => Driver::STATUS_ACTIVE]);
        $inactiveSameBranch = Driver::factory()->for($branch)->create(['full_name' => 'Driver Nonaktif', 'status' => Driver::STATUS_INACTIVE]);
        $otherBranchDriver = Driver::factory()->for($otherBranch)->create(['full_name' => 'Driver Cabang Lain', 'status' => Driver::STATUS_ACTIVE]);

        $this->get(route('passenger.rating.drivers', $vehicle->qr_token))
            ->assertOk()
            ->assertSee($activeSameBranch->full_name)
            ->assertDontSee($inactiveSameBranch->full_name)
            ->assertDontSee($otherBranchDriver->full_name);

        $this->get(route('passenger.rating.driver', [$vehicle->qr_token, $otherBranchDriver]))->assertNotFound();
    }

    public function test_empty_driver_and_vehicle_photos_use_default_assets(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['photo' => null]);
        $driver = Driver::factory()->for($branch)->create(['photo' => null]);

        $this->assertFileExists(public_path('images/defaults/driver.png'));
        $this->assertFileExists(public_path('images/defaults/vehicle.png'));

        $this->get(route('passenger.rating.entry', $vehicle->qr_token))
            ->assertOk()
            ->assertSee(asset('images/defaults/vehicle.png'), false);

        $this->get(route('passenger.rating.drivers', $vehicle->qr_token))
            ->assertOk()
            ->assertSee($driver->full_name)
            ->assertSee(asset('images/defaults/driver.png'), false);
    }

    public function test_assessment_only_shows_active_questions_ordered(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        $driver = Driver::factory()->for($branch)->create(['status' => Driver::STATUS_ACTIVE]);
        Question::factory()->create(['question' => 'Ketiga', 'sort_order' => 3, 'status' => Question::STATUS_ACTIVE]);
        Question::factory()->create(['question' => 'Pertama', 'sort_order' => 1, 'status' => Question::STATUS_ACTIVE]);
        Question::factory()->create(['question' => 'Nonaktif', 'sort_order' => 2, 'status' => Question::STATUS_INACTIVE]);
        Question::factory()->create(['question' => 'Kedua', 'sort_order' => 2, 'status' => Question::STATUS_ACTIVE]);

        $this->post(route('passenger.rating.assessor.store', [$vehicle->qr_token, $driver]), ['passenger_name' => 'Penilai Uji', 'passenger_unit' => 'Unit Operasional']);

        $this->get(route('passenger.rating.assessment', [$vehicle->qr_token, $driver]))
            ->assertOk()
            ->assertSee('Pertama')
            ->assertSee('Kedua')
            ->assertSee('Ketiga')
            ->assertDontSee('Nonaktif');
    }

    public function test_submit_validates_required_rating_yes_no_and_option_ownership(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        $driver = Driver::factory()->for($branch)->create(['status' => Driver::STATUS_ACTIVE]);
        $rating = Question::factory()->create(['answer_type' => Question::TYPE_RATING, 'is_required' => true, 'status' => Question::STATUS_ACTIVE]);
        $yesNo = Question::factory()->create(['answer_type' => Question::TYPE_YES_NO, 'is_required' => true, 'status' => Question::STATUS_ACTIVE]);
        $choice = Question::factory()->create(['answer_type' => Question::TYPE_MULTIPLE_CHOICE, 'is_required' => true, 'status' => Question::STATUS_ACTIVE]);
        $otherChoice = Question::factory()->create(['answer_type' => Question::TYPE_MULTIPLE_CHOICE, 'is_required' => true, 'status' => Question::STATUS_ACTIVE]);
        $invalidOption = $otherChoice->options()->create(['option_text' => 'Milik pertanyaan lain', 'sort_order' => 1]);

        $this->from(route('passenger.rating.assessment', [$vehicle->qr_token, $driver]))
            ->post(route('passenger.rating.submit', [$vehicle->qr_token, $driver]), [
                'passenger_name' => 'Penilai Uji',
                'passenger_unit' => 'Unit Operasional',
                'answers' => [
                    $rating->id => '6',
                    $yesNo->id => '2',
                    $choice->id => (string) $invalidOption->id,
                ],
            ])->assertSessionHasErrors(["answers.{$rating->id}", "answers.{$yesNo->id}", "answers.{$choice->id}"]);
    }

    public function test_assessment_requires_passenger_name_step(): void
    {
        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);
        $driver = Driver::factory()->for($branch)->create(['status' => Driver::STATUS_ACTIVE]);

        $this->get(route('passenger.rating.assessment', [$vehicle->qr_token, $driver]))
            ->assertRedirect(route('passenger.rating.assessor', [$vehicle->qr_token, $driver]));

        $this->from(route('passenger.rating.assessor', [$vehicle->qr_token, $driver]))
            ->post(route('passenger.rating.assessor.store', [$vehicle->qr_token, $driver]), ['passenger_name' => ' ', 'passenger_unit' => 'Kantor Pusat'])
            ->assertSessionHasErrors('passenger_name');
    }

    public function test_inactive_vehicle_is_rejected_in_passenger_flow(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => Vehicle::STATUS_INACTIVE]);

        $this->get(route('passenger.rating.entry', $vehicle->qr_token))->assertForbidden();
    }
}
