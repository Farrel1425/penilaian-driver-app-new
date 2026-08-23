<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\RatingAnswer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_two_tables_exist_with_required_columns(): void
    {
        foreach (['branches', 'drivers', 'vehicles', 'questions', 'question_options', 'ratings', 'rating_answers'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
        }

        $this->assertTrue(Schema::hasColumns('branches', ['code', 'name', 'address', 'regency', 'pic_name', 'phone', 'email', 'status']));
        $this->assertTrue(Schema::hasColumns('drivers', ['branch_id', 'full_name', 'status']));
        $this->assertFalse(Schema::hasColumn('drivers', 'vehicle_id'));
        $this->assertTrue(Schema::hasColumns('vehicles', ['branch_id', 'police_number', 'qr_token', 'status']));
        $this->assertTrue(Schema::hasColumns('questions', ['target_type', 'answer_type', 'is_required', 'sort_order', 'status']));
        $this->assertTrue(Schema::hasColumns('rating_answers', ['answer_value', 'answer_text']));
    }

    public function test_branch_driver_vehicle_and_rating_relationships_work(): void
    {
        $branch = Branch::factory()->create();
        $driver = Driver::factory()->for($branch)->create();
        $vehicle = Vehicle::factory()->for($branch)->create();
        $question = Question::factory()->create([
            'target_type' => Question::TARGET_DRIVER,
            'answer_type' => Question::TYPE_RATING,
            'sort_order' => 1,
        ]);

        $rating = Rating::factory()->for($branch)->for($driver)->for($vehicle)->create();
        $answer = RatingAnswer::factory()->for($rating)->for($question)->create([
            'answer_value' => [5],
        ]);

        $this->assertTrue($branch->drivers->contains($driver));
        $this->assertTrue($branch->vehicles->contains($vehicle));
        $this->assertTrue($branch->ratings->contains($rating));
        $this->assertTrue($driver->ratings->contains($rating));
        $this->assertTrue($vehicle->ratings->contains($rating));
        $this->assertTrue($question->ratingAnswers->contains($answer));
        $this->assertSame($branch->id, $rating->branch->id);
        $this->assertSame($driver->id, $rating->driver->id);
        $this->assertSame($vehicle->id, $rating->vehicle->id);
        $this->assertSame([5], $answer->answer_value);
    }

    public function test_questions_can_be_filtered_active_and_ordered(): void
    {
        Question::factory()->create(['question' => 'Inactive', 'sort_order' => 1, 'status' => Question::STATUS_INACTIVE]);
        Question::factory()->create(['question' => 'Second', 'sort_order' => 2, 'status' => Question::STATUS_ACTIVE]);
        Question::factory()->create(['question' => 'First', 'sort_order' => 1, 'status' => Question::STATUS_ACTIVE]);

        $questions = Question::query()->active()->ordered()->pluck('question')->all();

        $this->assertSame(['First', 'Second'], $questions);
    }
}
