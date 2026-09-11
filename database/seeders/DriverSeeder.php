<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\EmployeeCategory;
use Database\Seeders\Support\SeederCsv;
use Illuminate\Database\Seeder;
use RuntimeException;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $category = EmployeeCategory::query()->updateOrCreate(
            ['name' => 'Driver'],
            ['requires_sim' => true, 'status' => EmployeeCategory::STATUS_ACTIVE],
        );

        $branchIds = Branch::query()->pluck('id', 'code');

        foreach (SeederCsv::rows('drivers.csv') as $row) {
            $branchId = $branchIds->get($row['branch_code']);

            if ($branchId === null) {
                throw new RuntimeException("Unit kerja {$row['branch_code']} untuk {$row['full_name']} tidak ditemukan.");
            }

            $values = [
                'branch_id' => $branchId,
                'employee_category_id' => $category->id,
                'gender' => $row['gender'],
                'address' => $row['address'],
                'phone' => $row['phone'],
                'end_date' => $row['end_date'],
            ];

            foreach (['email', 'join_date'] as $optionalField) {
                if ($row[$optionalField] !== null) {
                    $values[$optionalField] = $row[$optionalField];
                }
            }

            $driver = Driver::query()
                ->where('full_name', $row['full_name'])
                ->whereDate('birth_date', $row['birth_date'])
                ->first() ?? new Driver([
                    'full_name' => $row['full_name'],
                    'birth_date' => $row['birth_date'],
                ]);

            $driver->fill($values)->save();
        }
    }
}
