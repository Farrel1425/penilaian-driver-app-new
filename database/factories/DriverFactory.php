<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Driver> */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'full_name' => $this->faker->name(),
            'nickname' => $this->faker->firstName(),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-22 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'marital_status' => $this->faker->randomElement(array_keys(Driver::MARITAL_STATUSES)),
            'sim_number' => $this->faker->unique()->numerify('SIM########'),
            'sim_type' => $this->faker->randomElement(['A', 'B1', 'B2']),
            'sim_expired_at' => $this->faker->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'join_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => Driver::STATUS_ACTIVE,
        ];
    }
}
