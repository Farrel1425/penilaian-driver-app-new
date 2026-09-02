<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class BulkBranchSeeder extends Seeder
{
    public function run(): void
    {
        $driverPhotos = Driver::query()
            ->whereHas('branch', fn ($query) => $query->where('code', 'not like', 'SIM-%'))
            ->whereNotNull('photo')
            ->where('photo', '<>', '')
            ->orderBy('id')
            ->pluck('photo')
            ->values();

        $vehiclePhotos = Vehicle::query()
            ->whereHas('branch', fn ($query) => $query->where('code', 'not like', 'SIM-%'))
            ->whereNotNull('photo')
            ->where('photo', '<>', '')
            ->orderBy('id')
            ->get(['photo', 'interior_photo'])
            ->values();

        foreach (range(1, 10) as $number) {
            $code = sprintf('SIM-%03d', $number);
            $branch = Branch::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => sprintf('Cabang Simulasi %02d', $number),
                    'address' => sprintf('Alamat Cabang Simulasi %02d, Bali', $number),
                    'regency' => Branch::BALI_REGENCIES[($number - 1) % count(Branch::BALI_REGENCIES)],
                    'pic_name' => sprintf('PIC Cabang Simulasi %02d', $number),
                    'phone' => sprintf('0361-81%04d', $number),
                    'email' => sprintf('cabang-simulasi-%02d@lais.test', $number),
                    'status' => Branch::STATUS_ACTIVE,
                ],
            );

            $missingDrivers = max(0, 10 - $branch->drivers()->count());
            if ($missingDrivers > 0) {
                Driver::factory()->count($missingDrivers)->for($branch)->create();
            }

            $missingVehicles = max(0, 10 - $branch->vehicles()->count());
            if ($missingVehicles > 0) {
                Vehicle::factory()->count($missingVehicles)->for($branch)->create();
            }

            if ($driverPhotos->isNotEmpty()) {
                $branch->drivers()->whereNull('photo')->oldest('id')->get()
                    ->each(fn (Driver $driver, int $index) => $driver->update([
                        'photo' => $driverPhotos[$index % $driverPhotos->count()],
                    ]));
            }

            if ($vehiclePhotos->isNotEmpty()) {
                $branch->vehicles()->whereNull('photo')->oldest('id')->get()
                    ->each(function (Vehicle $vehicle, int $index) use ($vehiclePhotos): void {
                        $source = $vehiclePhotos[$index % $vehiclePhotos->count()];
                        $vehicle->update([
                            'photo' => $source->photo,
                            'interior_photo' => $source->interior_photo,
                        ]);
                    });
            }
        }
    }
}