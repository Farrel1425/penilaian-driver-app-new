<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Branch> */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = $this->faker->city();

        return [
            'code' => strtoupper(Str::slug(substr($name, 0, 3))).'-'.$this->faker->unique()->numerify('###'),
            'name' => 'Cabang '.$name,
            'address' => $this->faker->address(),
            'regency' => $this->faker->randomElement(Branch::BALI_REGENCIES),
            'pic_name' => $this->faker->name(),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => Branch::STATUS_ACTIVE,
        ];
    }
}
