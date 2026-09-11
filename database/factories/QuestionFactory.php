<?php

namespace Database\Factories;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(),
            'target_type' => $this->faker->randomElement([Question::TARGET_DRIVER, Question::TARGET_VEHICLE]),
            'indicator_category_id' => fn (array $attributes) => IndicatorCategory::factory()->create([
                'target_type' => $attributes['target_type'],
            ])->id,
            'answer_type' => $this->faker->randomElement([
                Question::TYPE_RATING,
                Question::TYPE_YES_NO,
                Question::TYPE_SHORT_TEXT,
                Question::TYPE_PARAGRAPH,
            ]),
            'is_required' => true,
            'weight' => 10,
            'sort_order' => $this->faker->numberBetween(1, 20),
            'status' => Question::STATUS_ACTIVE,
        ];
    }
}
