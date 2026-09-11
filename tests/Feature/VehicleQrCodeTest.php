<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleQrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_qr_token_opens_vehicle_entry(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => Vehicle::STATUS_ACTIVE]);

        $this->get(route('passenger.rating.entry', $vehicle->qr_token))
            ->assertOk()
            ->assertSee($vehicle->police_number);
    }

    public function test_invalid_qr_token_returns_not_found(): void
    {
        $this->get(route('passenger.rating.entry', 'invalid-token'))
            ->assertNotFound();
    }

    public function test_inactive_vehicle_qr_is_rejected(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => Vehicle::STATUS_INACTIVE]);

        $this->get(route('passenger.rating.entry', $vehicle->qr_token))
            ->assertForbidden();
    }

    public function test_admin_can_regenerate_vehicle_qr_token(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->create();
        $oldToken = $vehicle->qr_token;

        $this->patch(route('admin.vehicles.regenerate-qr', $vehicle))->assertRedirect();

        $this->assertNotSame($oldToken, $vehicle->fresh()->qr_token);
        $this->assertSame(40, strlen($vehicle->fresh()->qr_token));
    }

    public function test_admin_can_download_print_ready_vehicle_qr_pdf(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->for(Branch::factory())->create();

        $this->get(route('admin.vehicles.qr.download', $vehicle))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=qr-kendaraan-'.str($vehicle->police_number)->slug()->toString().'.pdf')
            ->assertSee('%PDF', false);
    }

    public function test_admin_can_open_vehicle_qr_print_page(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->for(Branch::factory())->create();

        $this->get(route('admin.vehicles.qr.print', $vehicle))
            ->assertOk()
            ->assertSee('Print QR')
            ->assertSee($vehicle->police_number)
            ->assertDontSee('/rating/'.$vehicle->qr_token, false);
    }

    public function test_admin_can_open_label_sized_qr_print_page(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->create();

        $this->get(route('admin.vehicles.qr.print', ['vehicle' => $vehicle, 'format' => 'label']))
            ->assertOk()
            ->assertSee('qr-print-card is-label', false);
    }

    public function test_admin_can_preview_vehicle_qr(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->create();

        $this->get(route('admin.vehicles.qr.preview', $vehicle))
            ->assertOk()
            ->assertSee('Preview QR')
            ->assertDontSee('/rating/'.$vehicle->qr_token, false)
            ->assertDontSee($vehicle->qr_token, false);
    }
}
