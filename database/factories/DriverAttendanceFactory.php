<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DriverAttendance> */
class DriverAttendanceFactory extends Factory
{
    protected $model = DriverAttendance::class;

    public function definition(): array
    {
        $branch = Branch::factory();

        return [
            'branch_id' => $branch,
            'driver_id' => Driver::factory()->for($branch),
            'period' => now()->startOfMonth(),
            'present_days' => 20,
            'sick_days' => 1,
            'permitted_days' => 1,
            'absent_days' => 0,
        ];
    }
}
