<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ClientQuestionSeeder::class);

        if (Branch::query()->doesntExist()) {
            foreach ([
                ['DPS-001', 'Cabang Denpasar', 'Kota Denpasar'],
                ['BDG-001', 'Cabang Badung', 'Kabupaten Badung'],
                ['GIA-001', 'Cabang Gianyar', 'Kabupaten Gianyar'],
            ] as [$code, $name, $regency]) {
                Branch::factory()->create(compact('code', 'name', 'regency'));
            }
        }

        $branches = Branch::query()->active()->with(['drivers', 'vehicles'])->get();

        foreach ($branches as $branch) {
            if ($branch->drivers->where('status', Driver::STATUS_ACTIVE)->isEmpty()) {
                Driver::factory()->count(3)->for($branch)->create(['photo' => null, 'sim_photo' => null]);
            }

            if ($branch->vehicles->where('status', Vehicle::STATUS_ACTIVE)->isEmpty()) {
                Vehicle::factory()->count(2)->for($branch)->create(['photo' => null, 'interior_photo' => null]);
            }
        }

        $questions = Question::query()->with('options')->active()->ordered()->get();
        $sequence = 0;

        Rating::query()->with('answers')->get()->each(function (Rating $rating) use ($questions, &$sequence): void {
            if ($rating->answers->isEmpty()) {
                $this->createAnswers($rating, $questions, $sequence++);
            }
        });

        Branch::query()->active()->each(function (Branch $branch) use ($questions, &$sequence): void {
            $drivers = $branch->drivers()->active()->take(3)->get();
            $vehicles = $branch->vehicles()->active()->take(2)->get();

            if ($drivers->isEmpty() || $vehicles->isEmpty()) {
                return;
            }

            foreach (range(0, 11) as $offset) {
                $rating = Rating::query()->create([
                    'branch_id' => $branch->id,
                    'driver_id' => $drivers[$offset % $drivers->count()]->id,
                    'vehicle_id' => $vehicles[$offset % $vehicles->count()]->id,
                    'submitted_at' => now()->subDays($offset * 2)->setTime(8 + ($offset % 9), 15),
                ]);

                $this->createAnswers($rating, $questions, $sequence++);
            }
        });
    }

    private function createAnswers(Rating $rating, $questions, int $sequence): void
    {
        $answers = [];

        foreach ($questions->where('answer_type', Question::TYPE_RATING) as $question) {
            $answers[] = [
                'question_id' => $question->id,
                'answer_value' => [3 + (($sequence + $question->sort_order) % 3)],
                'answer_text' => null,
            ];
        }

        $feedback = $questions->first(fn (Question $question): bool => $question->target_type === Question::TARGET_FEEDBACK
            && $question->answer_type === Question::TYPE_MULTIPLE_CHOICE);
        $followUp = $questions->first(fn (Question $question): bool => $question->target_type === Question::TARGET_FEEDBACK
            && $question->answer_type === Question::TYPE_PARAGRAPH);

        if ($feedback) {
            $option = $feedback->options->firstWhere('sort_order', $sequence % 4 === 0 ? 2 : 1);
            $answers[] = ['question_id' => $feedback->id, 'answer_value' => [$option->id], 'answer_text' => null];

            if ($option->sort_order > 1 && $followUp) {
                $answers[] = [
                    'question_id' => $followUp->id,
                    'answer_value' => null,
                    'answer_text' => 'Mohon dilakukan evaluasi dan tindak lanjut terhadap pelayanan driver.',
                ];
            }
        }

        $rating->answers()->createMany($answers);
    }
}
