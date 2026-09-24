<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleQrCodeService;
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

    public function test_vehicle_qr_url_uses_the_active_app_url(): void
    {
        config(['app.url' => 'https://penilaian.example.test']);
        $vehicle = Vehicle::factory()->create(['qr_token' => 'token-kendaraan']);

        $this->assertSame(
            'https://penilaian.example.test/rating/token-kendaraan',
            app(VehicleQrCodeService::class)->vehicleUrl($vehicle),
        );
    }

    public function test_vehicle_qr_svg_embeds_the_company_logo_as_data_without_external_asset_url(): void
    {
        $vehicle = Vehicle::factory()->create();
        $svg = app(VehicleQrCodeService::class)->svg($vehicle);

        $this->assertStringContainsString('<image ', $svg);
        $this->assertStringContainsString('href="data:image/png;base64,', $svg);
        $this->assertStringNotContainsString(public_path(), $svg);
    }

    public function test_vehicle_qr_svg_logo_is_centered_against_the_actual_viewbox(): void
    {
        $vehicle = Vehicle::factory()->create();
        $svg = app(VehicleQrCodeService::class)->svg($vehicle);

        $this->assertSame(1, preg_match('/viewBox="0 0 ([0-9.]+) [0-9.]+"/', $svg, $viewBox));
        $this->assertSame(1, preg_match('/<image x="([0-9.]+)" y="[0-9.]+" width="([0-9]+)"/', $svg, $logo));

        $this->assertEqualsWithDelta(
            ((float) $viewBox[1]) / 2,
            (float) $logo[1] + (((float) $logo[2]) / 2),
            0.01,
        );
    }

    public function test_vehicle_qr_uses_an_optimized_logo_asset(): void
    {
        $path = public_path('images/bds/bds-logo-qr.png');
        $size = getimagesize($path);

        $this->assertIsArray($size);
        $this->assertSame(128, $size[0]);
        $this->assertSame(128, $size[1]);
        $this->assertLessThan(100_000, filesize($path));
    }

    public function test_vehicle_index_with_ten_qr_codes_stays_within_a_reasonable_response_size(): void
    {
        $this->actingAs(User::factory()->create());
        Vehicle::factory()->count(10)->create();

        $response = $this->get(route('admin.vehicles.index'))->assertOk();

        $this->assertLessThan(5 * 1024 * 1024, strlen($response->getContent()));
    }

    public function test_vehicle_qr_poster_asset_has_the_expected_dimensions(): void
    {
        $size = getimagesize(public_path('images/qr-vehicle-poster.png'));

        $this->assertIsArray($size);
        $this->assertSame('image/png', $size['mime']);
        $this->assertSame(700, $size[0]);
        $this->assertSame(1000, $size[1]);
        $this->assertFileDoesNotExist(public_path('images/qr-vehicle-template.png'));
    }

    public function test_vehicle_qr_png_contains_the_company_logo_in_its_center(): void
    {
        $vehicle = Vehicle::factory()->create();
        $png = app(VehicleQrCodeService::class)->png($vehicle, size: 320);
        $image = imagecreatefromstring($png);

        $this->assertNotFalse($image);

        $center = imagecolorsforindex($image, imagecolorat($image, 160, 160));

        $this->assertFalse(
            ($center['red'] < 20 && $center['green'] < 20 && $center['blue'] < 20)
            || ($center['red'] > 245 && $center['green'] > 245 && $center['blue'] > 245),
        );

        imagedestroy($image);
    }

    public function test_admin_can_download_vehicle_qr_as_exact_size_png(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->for(Branch::factory())->create();

        $response = $this->get(route('admin.vehicles.qr.download', $vehicle))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'attachment; filename=qr-kendaraan-'.str($vehicle->police_number)->slug()->toString().'.png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertIsArray($size);
        $this->assertSame(700, $size[0]);
        $this->assertSame(1000, $size[1]);
    }

    public function test_vehicle_detail_keeps_qr_popup_and_only_download_and_regenerate_actions(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->for(Branch::factory())->create();

        $this->get(route('admin.vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('data-vehicle-qr-modal', false)
            ->assertSee('data-vehicle-qr-trigger', false)
            ->assertSee('data-vehicle-qr-download-link', false)
            ->assertSee('data-qr-download-filename="qr-kendaraan-'.str($vehicle->police_number)->slug().'.png"', false)
            ->assertSee('data-confirm-icon="refresh"', false)
            ->assertSee(route('admin.vehicles.qr.download', $vehicle), false)
            ->assertSee('Download QR')
            ->assertSee('Regenerate QR')
            ->assertDontSee('Atur &amp; Cetak QR', false)
            ->assertDontSee('data-vehicle-qr-print-modal', false)
            ->assertDontSee('data-vehicle-qr-print-open', false)
            ->assertDontSee('Preview QR')
            ->assertDontSee('target="_blank"', false);
    }
}
