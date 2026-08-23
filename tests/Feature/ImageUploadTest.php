<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_driver_photo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->post(route('admin.drivers.store'), [
            'branch_id' => $branch->id,
            'full_name' => 'Driver Dengan Foto',
            'join_date' => '2026-01-01',
            'photo' => UploadedFile::fake()->image('driver.jpg', 800, 1000),
            'status' => Driver::STATUS_ACTIVE,
        ])->assertRedirect();

        $driver = Driver::query()->where('full_name', 'Driver Dengan Foto')->firstOrFail();

        $this->assertStringStartsWith('drivers/', $driver->photo);
        Storage::disk('public')->assertExists($driver->photo);
    }

    public function test_admin_can_replace_vehicle_photo_and_old_file_is_removed(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();
        $oldPhoto = UploadedFile::fake()->image('old.jpg')->store('vehicles', 'public');
        $vehicle = Vehicle::factory()->for($branch)->create(['photo' => $oldPhoto]);

        $this->put(route('admin.vehicles.update', $vehicle), [
            'branch_id' => $branch->id,
            'police_number' => $vehicle->police_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'status' => Vehicle::STATUS_ACTIVE,
            'photo' => UploadedFile::fake()->image('new.jpg', 1600, 900),
        ])->assertRedirect(route('admin.vehicles.index'));

        $vehicle->refresh();

        Storage::disk('public')->assertMissing($oldPhoto);
        $this->assertStringStartsWith('vehicles/', $vehicle->photo);
        Storage::disk('public')->assertExists($vehicle->photo);
    }
}
