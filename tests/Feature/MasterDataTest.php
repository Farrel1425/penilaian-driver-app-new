<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_pages_require_authentication(): void
    {
        $this->get(route('admin.branches.index'))->assertRedirect(route('login'));
        $this->get(route('admin.drivers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_update_and_deactivate_branch(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('admin.branches.store'), [
            'code' => 'TST-001',
            'name' => 'Cabang Test',
            'address' => 'Alamat Test',
            'regency' => 'Kota Denpasar',
            'pic_name' => 'Putri Admin',
            'phone' => '081234567890',
            'email' => 'cabang-test@example.com',
            'status' => Branch::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->where('code', 'TST-001')->firstOrFail();
        $response->assertRedirect(route('admin.branches.show', $branch));

        $this->put(route('admin.branches.update', $branch), [
            'code' => 'TST-002',
            'name' => 'Cabang Update',
            'address' => 'Alamat Update',
            'regency' => 'Kabupaten Badung',
            'pic_name' => 'Made PIC',
            'phone' => '081234567891',
            'email' => 'cabang-update@example.com',
            'status' => Branch::STATUS_ACTIVE,
        ])->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'regency' => 'Kabupaten Badung',
            'pic_name' => 'Made PIC',
            'phone' => '081234567891',
            'email' => 'cabang-update@example.com',
        ]);

        $this->patch(route('admin.branches.toggle-status', $branch))->assertRedirect();
        $this->assertSame(Branch::STATUS_INACTIVE, $branch->fresh()->status);
    }

    public function test_deactivating_branch_also_deactivates_its_drivers_and_vehicles(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create(['status' => Branch::STATUS_ACTIVE]);
        $driver = Driver::factory()->for($branch)->create(['status' => Driver::STATUS_ACTIVE]);
        $vehicle = Vehicle::factory()->for($branch)->create(['status' => Vehicle::STATUS_ACTIVE]);

        $this->patch(route('admin.branches.toggle-status', $branch))->assertRedirect();

        $this->assertSame(Branch::STATUS_INACTIVE, $branch->fresh()->status);
        $this->assertSame(Driver::STATUS_INACTIVE, $driver->fresh()->status);
        $this->assertSame(Vehicle::STATUS_INACTIVE, $vehicle->fresh()->status);

        $this->patch(route('admin.branches.toggle-status', $branch->fresh()))->assertRedirect();

        $this->assertSame(Branch::STATUS_ACTIVE, $branch->fresh()->status);
        $this->assertSame(Driver::STATUS_INACTIVE, $driver->fresh()->status);
        $this->assertSame(Vehicle::STATUS_INACTIVE, $vehicle->fresh()->status);
    }

    public function test_branch_requires_valid_bali_regency_and_contact_information(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.branches.store'), [
            'code' => 'TST-003',
            'name' => 'Cabang Tidak Valid',
            'address' => 'Alamat Test',
            'regency' => 'Kabupaten Lain',
            'pic_name' => 'PIC Test',
            'phone' => 'telepon',
            'email' => 'email-tidak-valid',
            'status' => Branch::STATUS_ACTIVE,
        ])->assertSessionHasErrors(['regency', 'phone', 'email']);
    }

    public function test_branch_edit_navigation_returns_to_its_origin(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->get(route('admin.branches.edit', $branch))
            ->assertOk()
            ->assertSee(route('admin.branches.index'), false)
            ->assertDontSee('<div class="page-header">', false);

        $this->get(route('admin.branches.edit', ['branch' => $branch, 'return_to' => 'detail']))
            ->assertOk()
            ->assertSee(route('admin.branches.show', $branch), false);

        $this->put(route('admin.branches.update', $branch), [
            'code' => $branch->code,
            'name' => $branch->name,
            'address' => $branch->address,
            'regency' => $branch->regency,
            'pic_name' => $branch->pic_name,
            'phone' => $branch->phone,
            'email' => $branch->email,
            'status' => $branch->status,
            'return_to' => 'detail',
        ])->assertRedirect(route('admin.branches.show', $branch));
    }

    public function test_admin_can_create_driver_without_vehicle_assignment(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->post(route('admin.drivers.store'), [
            'branch_id' => $branch->id,
            'full_name' => 'Budi Driver',
            'nickname' => 'Budi',
            'birth_place' => 'Denpasar',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Denpasar',
            'phone' => '08123456789',
            'email' => 'budi@example.com',
            'photo' => null,
            'sim_number' => 'SIM123',
            'sim_type' => 'A',
            'sim_expired_at' => '2030-01-01',
            'sim_photo' => null,
            'join_date' => '2026-01-01',
            'status' => Driver::STATUS_ACTIVE,
        ])->assertRedirect();

        $driver = Driver::query()->where('full_name', 'Budi Driver')->firstOrFail();

        $this->assertSame($branch->id, $driver->branch_id);
        $this->assertFalse(Schema::hasColumn('drivers', 'vehicle_id'));
    }

    public function test_driver_join_date_is_required_while_sim_details_are_optional(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->post(route('admin.drivers.store'), [
            'branch_id' => $branch->id,
            'full_name' => 'Driver Tanpa Tanggal Bergabung',
            'status' => Driver::STATUS_ACTIVE,
        ])->assertSessionHasErrors('join_date');

        $this->post(route('admin.drivers.store'), [
            'branch_id' => $branch->id,
            'full_name' => 'Driver Dengan Tanggal Bergabung',
            'birth_place' => 'Denpasar',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Jalan Contoh 1',
            'phone' => '081234567890',
            'join_date' => '2026-01-01',
            'status' => Driver::STATUS_ACTIVE,
        ])->assertRedirect();
    }

    public function test_admin_can_create_vehicle_and_regenerate_qr_token(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->post(route('admin.vehicles.store'), [
            'branch_id' => $branch->id,
            'police_number' => 'DK 1234 AB',
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'year' => 2024,
            'color' => 'Putih',
            'chassis_number' => null,
            'engine_number' => null,
            'fuel_type' => 'gasoline',
            'transmission' => 'manual',
            'passenger_capacity' => 6,
            'acquisition_date' => null,
            'acquisition_source' => null,
            'ownership_type' => 'owned',
            'contract_number' => null,
            'contract_expired_at' => null,
            'description' => null,
            'photo' => null,
            'status' => Vehicle::STATUS_ACTIVE,
        ])->assertRedirect();

        $vehicle = Vehicle::query()->where('police_number', 'DK 1234 AB')->firstOrFail();
        $oldToken = $vehicle->qr_token;

        $this->assertNotEmpty($oldToken);

        $this->patch(route('admin.vehicles.regenerate-qr', $vehicle))->assertRedirect();

        $this->assertNotSame($oldToken, $vehicle->fresh()->qr_token);
    }

    public function test_vehicle_ownership_is_limited_to_company_or_rental(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();

        $this->post(route('admin.vehicles.store'), [
            'branch_id' => $branch->id,
            'police_number' => 'DK 6789 CD',
            'brand' => 'Toyota',
            'ownership_type' => 'personal',
            'status' => Vehicle::STATUS_ACTIVE,
        ])->assertSessionHasErrors('ownership_type');
    }

    public function test_admin_master_data_pages_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());
        $branch = Branch::factory()->create();
        $driver = Driver::factory()->for($branch)->create();
        $vehicle = Vehicle::factory()->for($branch)->create();

        $this->get(route('admin.branches.index'))->assertOk();
        $this->get(route('admin.branches.create'))
            ->assertOk()
            ->assertDontSee('<div class="page-header">', false)
            ->assertDontSee('Informasi Cabang');
        $this->get(route('admin.branches.show', $branch))->assertOk();
        $this->get(route('admin.branches.edit', $branch))->assertOk();

        $this->get(route('admin.drivers.index'))->assertOk();
        $this->get(route('admin.drivers.create'))->assertOk();
        $this->get(route('admin.drivers.show', $driver))->assertOk();
        $this->get(route('admin.drivers.edit', $driver))->assertOk();

        $this->get(route('admin.vehicles.index'))->assertOk();
        $this->get(route('admin.vehicles.create'))->assertOk();
        $this->get(route('admin.vehicles.show', $vehicle))->assertOk();
        $this->get(route('admin.vehicles.edit', $vehicle))->assertOk();
    }

    public function test_vehicle_list_includes_qr_popup_and_download_link(): void
    {
        $this->actingAs(User::factory()->create());
        $vehicle = Vehicle::factory()->for(Branch::factory())->create();

        $this->get(route('admin.vehicles.index'))
            ->assertOk()
            ->assertSee('data-vehicle-qr-modal', false)
            ->assertSee(route('admin.vehicles.qr.download', $vehicle), false);
    }

    public function test_master_lists_can_filter_complete_and_incomplete_records(): void
    {
        $this->actingAs(User::factory()->create());

        $completeBranch = Branch::factory()->create(['name' => 'Unit Lengkap']);
        $incompleteBranch = Branch::factory()->create(['name' => 'Unit Belum Lengkap', 'pic_name' => null]);

        $this->get(route('admin.branches.index', ['completeness' => 'complete']))
            ->assertOk()
            ->assertSee($completeBranch->name)
            ->assertDontSee($incompleteBranch->name);
        $this->get(route('admin.branches.index', ['completeness' => 'incomplete']))
            ->assertOk()
            ->assertSee($incompleteBranch->name)
            ->assertDontSee($completeBranch->name);

        $completeDriver = Driver::factory()->for($completeBranch)->create([
            'full_name' => 'Pegawai Lengkap',
            'sim_photo' => 'driver-sims/sim-lengkap.jpg',
        ]);
        $incompleteDriver = Driver::factory()->for($completeBranch)->create([
            'full_name' => 'Pegawai Belum Lengkap',
            'birth_place' => null,
        ]);

        $this->get(route('admin.employees.index', ['completeness' => 'complete']))
            ->assertOk()
            ->assertSee($completeDriver->full_name)
            ->assertDontSee($incompleteDriver->full_name);
        $this->get(route('admin.employees.index', ['completeness' => 'incomplete']))
            ->assertOk()
            ->assertSee($incompleteDriver->full_name)
            ->assertDontSee($completeDriver->full_name);

        $completeVehicle = Vehicle::factory()->for($completeBranch)->create([
            'police_number' => 'DK 1000 OK',
            'acquisition_source' => 'purchase',
            'ownership_type' => Vehicle::OWNERSHIP_COMPANY,
            'stnk_expired_at' => '2028-01-01',
            'kir_expired_at' => '2028-01-01',
            'photo' => 'vehicles/exterior/lengkap.jpg',
            'interior_photo' => 'vehicles/interior/lengkap.jpg',
        ]);
        $incompleteVehicle = Vehicle::factory()->for($completeBranch)->create([
            'police_number' => 'DK 2000 NO',
            'chassis_number' => null,
        ]);

        $this->get(route('admin.vehicles.index', ['completeness' => 'complete']))
            ->assertOk()
            ->assertSee($completeVehicle->police_number)
            ->assertDontSee($incompleteVehicle->police_number);
        $this->get(route('admin.vehicles.index', ['completeness' => 'incomplete']))
            ->assertOk()
            ->assertSee($incompleteVehicle->police_number)
            ->assertDontSee($completeVehicle->police_number);
    }
}
