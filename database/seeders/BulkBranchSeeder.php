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
        }
    }
}