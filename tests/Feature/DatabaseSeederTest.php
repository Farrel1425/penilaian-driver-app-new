<?php

namespace Tests\Feature;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_integrated_question_data_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(9, IndicatorCategory::query()->count());
        $this->assertSame(16, Question::query()->count());
        $this->assertSame(0, Question::query()->whereNull('indicator_category_id')->count());
        $this->assertSame(100, (int) Question::query()->where('target_type', Question::TARGET_DRIVER)->sum('weight'));
        $this->assertSame(100, (int) Question::query()->where('target_type', Question::TARGET_VEHICLE)->sum('weight'));
    }
}
