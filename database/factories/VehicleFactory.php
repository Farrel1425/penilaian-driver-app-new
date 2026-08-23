<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vehicle> */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'police_number' => strtoupper($this->faker->unique()->bothify('? #### ??')),
            'brand' => $this->faker->randomElement(['Toyota', 'Daihatsu', 'Suzuki', 'Mitsubishi']),
            'model' => $this->faker->randomElement(['Avanza', 'Xenia', 'Ertiga', 'L300']),
            'year' => $this->faker->numberBetween(2016, 2026),
            'color' => $this->faker->safeColorName(),
            'chassis_number' => $this->faker->unique()->bothify('CHS############'),
            'engine_number' => $this->faker->unique()->bothify('ENG############'),
            'fuel_type' => $this->faker->randomElement(array_keys(Vehicle::FUEL_TYPES)),
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'passenger_capacity' => $this->faker->numberBetween(4, 16),
            'acquisition_date' => $this->faker->dateTimeBetween('-8 years', 'now')->format('Y-m-d'),
            'ownership_type' => $this->faker->randomElement(['owned', 'rental']),
            'status' => Vehicle::STATUS_ACTIVE,
            'qr_token' => Str::random(40),
        ];
    }
}
