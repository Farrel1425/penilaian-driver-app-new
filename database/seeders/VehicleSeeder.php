<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Vehicle;
use Database\Seeders\Support\SeederCsv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds = Branch::query()->pluck('id', 'code');

        foreach (SeederCsv::rows('vehicles.csv') as $row) {
            $branchId = $branchIds->get($row['branch_code']);

            if ($branchId === null) {
                throw new RuntimeException("Unit kerja {$row['branch_code']} untuk {$row['police_number']} tidak ditemukan.");
            }

            $values = [
                'branch_id' => $branchId,
                'brand' => $row['brand'],
                'model' => $row['model'],
            ];

            if ($row['year'] !== null) {
                $values['year'] = (int) $row['year'];
            }

            $vehicle = Vehicle::query()->firstOrNew([
                'police_number' => $row['police_number'],
            ]);

            if (! $vehicle->exists) {
                $vehicle->qr_token = Str::random(40);
            }

            $vehicle->fill($values)->save();
        }
    }
}
