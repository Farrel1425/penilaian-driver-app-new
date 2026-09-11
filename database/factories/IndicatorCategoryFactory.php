<?php

namespace Database\Factories;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IndicatorCategory> */
class IndicatorCategoryFactory extends Factory
{
    protected $model = IndicatorCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'target_type' => Question::TARGET_DRIVER,
            'status' => IndicatorCategory::STATUS_ACTIVE,
            'sort_order' => $this->faker->numberBetween(1, 20),
        ];
    }
}
