<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Rating;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class CompleteDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Driver::query()->orderBy('id')->each(function (Driver $driver): void {
            $suffix = str_pad((string) $driver->id, 3, '0', STR_PAD_LEFT);

            $driver->fill([
                'birth_place' => $driver->birth_place ?: 'Denpasar',
                'birth_date' => $driver->birth_date ?: now()->subYears(28 + ($driver->id % 12))->subDays($driver->id * 17)->toDateString(),
                'gender' => $driver->gender ?: ($driver->id % 2 === 0 ? 'female' : 'male'),
                'address' => $driver->address ?: "Jl. Transportasi No. {$driver->id}, Bali",
                'phone' => $driver->phone ?: "0812{$suffix}5678",
                'email' => $driver->email ?: "driver{$driver->id}@demo.local",
                'marital_status' => $driver->marital_status ?: ($driver->id % 2 === 0 ? 'married' : 'single'),
                'sim_number' => $driver->sim_number ?: "SIM-A-2026-{$suffix}",
                'sim_type' => $driver->sim_type ?: 'A',
                'sim_expired_at' => $driver->sim_expired_at ?: now()->addYears(3)->addDays($driver->id)->toDateString(),
                'join_date' => $driver->join_date ?: now()->subYears(1 + ($driver->id % 5))->subDays($driver->id * 9)->toDateString(),
            ])->save();
        });

        Vehicle::query()->orderBy('id')->each(function (Vehicle $vehicle): void {
            $suffix = str_pad((string) $vehicle->id, 4, '0', STR_PAD_LEFT);

            $vehicle->fill([
                'chassis_number' => $vehicle->chassis_number ?: "MHDEMOCH{$suffix}2026",
                'engine_number' => $vehicle->engine_number ?: "ENDEMO{$suffix}2026",
                'fuel_type' => $vehicle->fuel_type ?: ($vehicle->id % 3 === 0 ? 'diesel' : 'gasoline'),
                'transmission' => $vehicle->transmission ?: ($vehicle->id % 2 === 0 ? 'automatic' : 'manual'),
                'passenger_capacity' => $vehicle->passenger_capacity ?: 6,
                'acquisition_date' => $vehicle->acquisition_date ?: now()->subYears(2 + ($vehicle->id % 4))->toDateString(),
                'acquisition_source' => $vehicle->acquisition_source ?: ($vehicle->id % 2 === 0 ? 'leasing' : 'purchase'),
                'ownership_type' => $vehicle->ownership_type ?: ($vehicle->id % 2 === 0 ? Vehicle::OWNERSHIP_RENTAL : Vehicle::OWNERSHIP_COMPANY),
                'contract_number' => $vehicle->contract_number ?: ($vehicle->id % 2 === 0 ? "LSE-2026-{$suffix}" : null),
                'contract_expired_at' => $vehicle->contract_expired_at ?: ($vehicle->id % 2 === 0 ? now()->addYears(2)->toDateString() : null),
                'stnk_expired_at' => $vehicle->stnk_expired_at ?: now()->addYears(1)->addDays($vehicle->id)->toDateString(),
                'kir_expired_at' => $vehicle->kir_expired_at ?: now()->addMonths(6)->addDays($vehicle->id)->toDateString(),
                'description' => $vehicle->description ?: 'Data kendaraan contoh untuk kebutuhan pengujian sistem penilaian.',
            ])->save();
        });

        $ratings = Rating::query()->orderBy('id')->get();
        $total = max(1, $ratings->count() - 1);

        $ratings->each(function (Rating $rating, int $index) use ($total): void {
            $daysAgo = 48 - (int) round(($index / $total) * 48);
            $rating->update([
                'submitted_at' => now()
                    ->subDays($daysAgo)
                    ->setTime(8 + ($index % 10), 10 + (($index * 7) % 45)),
            ]);
        });
    }
}
