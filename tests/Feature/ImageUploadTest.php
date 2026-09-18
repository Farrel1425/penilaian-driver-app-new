<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
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
            'birth_place' => 'Denpasar',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Jalan Contoh 1',
            'phone' => '081234567890',
            'join_date' => '2026-01-01',
            'photo' => UploadedFile::fake()->image('driver.jpg', 800, 1000),
            'sim_photo' => UploadedFile::fake()->image('sim.jpg', 900, 600),
            'status' => Driver::STATUS_ACTIVE,
        ])->assertRedirect();

        $driver = Driver::query()->where('full_name', 'Driver Dengan Foto')->firstOrFail();

        $this->assertStringStartsWith('drivers/', $driver->photo);
        $this->assertStringStartsWith('driver-sims/', $driver->sim_photo);
        Storage::disk('public')->assertExists($driver->photo);
        Storage::disk('public')->assertExists($driver->sim_photo);
    }

    public function test_admin_can_replace_vehicle_photo_and_old_file_is_removed(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();
        $oldPhoto = UploadedFile::fake()->image('old.jpg')->store('vehicles', 'public');
        $oldInterior = UploadedFile::fake()->image('old-interior.jpg')->store('vehicles/interior', 'public');
        $vehicle = Vehicle::factory()->for($branch)->create(['photo' => $oldPhoto, 'interior_photo' => $oldInterior]);

        $this->put(route('admin.vehicles.update', $vehicle), [
            'branch_id' => $branch->id,
            'police_number' => $vehicle->police_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'status' => Vehicle::STATUS_ACTIVE,
            'photo' => UploadedFile::fake()->image('new.jpg', 1600, 900),
            'interior_photo' => UploadedFile::fake()->image('new-interior.jpg', 1600, 900),
        ])->assertRedirect(route('admin.vehicles.index'));

        $vehicle->refresh();

        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertMissing($oldInterior);
        $this->assertStringStartsWith('vehicles/', $vehicle->photo);
        $this->assertStringStartsWith('vehicles/interior/', $vehicle->interior_photo);
        Storage::disk('public')->assertExists($vehicle->photo);
        Storage::disk('public')->assertExists($vehicle->interior_photo);
    }

    public function test_admin_can_remove_vehicle_photos_and_files_are_removed(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();
        $photo = UploadedFile::fake()->image('exterior.jpg')->store('vehicles/exterior', 'public');
        $interior = UploadedFile::fake()->image('interior.jpg')->store('vehicles/interior', 'public');
        $vehicle = Vehicle::factory()->for($branch)->create(['photo' => $photo, 'interior_photo' => $interior]);

        $this->put(route('admin.vehicles.update', $vehicle), [
            'branch_id' => $branch->id,
            'police_number' => $vehicle->police_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'status' => Vehicle::STATUS_ACTIVE,
            'remove_photo' => 1,
            'remove_interior_photo' => 1,
        ])->assertRedirect(route('admin.vehicles.index'));

        $vehicle->refresh();
        $this->assertNull($vehicle->photo);
        $this->assertNull($vehicle->interior_photo);
        Storage::disk('public')->assertMissing([$photo, $interior]);
    }

    public function test_orphan_image_cleanup_keeps_referenced_files_and_requires_delete_option(): void
    {
        Storage::fake('public');
        $branch = Branch::factory()->create();
        $referenced = UploadedFile::fake()->image('used.jpg')->store('vehicles/exterior', 'public');
        $orphan = UploadedFile::fake()->image('unused.jpg')->store('vehicles/exterior', 'public');
        Vehicle::factory()->for($branch)->create(['photo' => $referenced]);

        $this->assertSame(0, Artisan::call('images:prune-orphans'));
        Storage::disk('public')->assertExists([$referenced, $orphan]);

        $this->assertSame(0, Artisan::call('images:prune-orphans', ['--delete' => true]));
        Storage::disk('public')->assertExists($referenced);
        Storage::disk('public')->assertMissing($orphan);
    }
}
