<?php

namespace Database\Seeders;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientQuestionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->createRatings(Question::TARGET_DRIVER, [
                ['Sikap & Etika', 'Apakah driver bersikap ramah, sopan, dan menghormati penumpang selama memberikan pelayanan?', 15],
                ['Keselamatan Mengemudi', 'Apakah driver mengemudi dengan aman, hati-hati, dan tidak membahayakan penumpang selama perjalanan?', 20],
                ['Kepatuhan', 'Apakah driver mematuhi peraturan lalu lintas dan rambu-rambu selama perjalanan?', 15],
                ['Ketepatan Waktu', 'Apakah driver datang dan menjemput penumpang sesuai dengan waktu yang telah disepakati?', 15],
                ['Komunikasi & Responsivitas', 'Apakah driver memberikan informasi yang jelas dan merespons kebutuhan penumpang dengan baik selama perjalanan?', 10],
                ['Pelaksanaan Penugasan', 'Apakah driver melaksanakan perjalanan sesuai tujuan dan penugasan yang diberikan?', 10],
                ['Penampilan', 'Apakah driver berpenampilan rapi, bersih, dan sesuai dengan standar pelayanan?', 15],
            ], 1);

            $this->createRatings(Question::TARGET_VEHICLE, [
                [Question::VEHICLE_INDICATOR, 'Apakah bagian dalam kendaraan bersih, rapi, dan nyaman digunakan?', 15],
                [Question::VEHICLE_INDICATOR, 'Apakah bagian luar kendaraan terlihat bersih dan terawat saat digunakan?', 10],
                [Question::VEHICLE_INDICATOR, 'Apakah AC/penyejuk udara berfungsi dengan baik dan memberikan kenyamanan selama perjalanan?', 10],
                [Question::VEHICLE_INDICATOR, 'Apakah tempat duduk dan ruang penumpang memberikan kenyamanan selama perjalanan?', 15],
                [Question::VEHICLE_INDICATOR, 'Apakah kendaraan memberikan rasa aman selama perjalanan?', 20],
                [Question::VEHICLE_INDICATOR, 'Apakah selama perjalanan kendaraan berfungsi dengan baik tanpa mengalami gangguan yang menghambat perjalanan?', 15],
                [Question::VEHICLE_INDICATOR, 'Secara keseluruhan, apakah kendaraan memenuhi standar pelayanan yang diharapkan?', 15],
            ], 8);

            $feedback = Question::query()->updateOrCreate([
                'target_type' => Question::TARGET_FEEDBACK,
                'sort_order' => 15,
            ], [
                'question' => 'Apakah terdapat kondisi kendaraan atau pelayanan driver yang menurut Anda perlu segera ditindaklanjuti?',
                'instruction' => 'Pilih kondisi yang paling sesuai dengan pengalaman Anda.',
                'placeholder' => null,
                'rating_min_label' => null,
                'rating_max_label' => null,
                'indicator_category_id' => $this->indicatorCategory(Question::TARGET_FEEDBACK, Question::FEEDBACK_INDICATOR),
                'answer_type' => Question::TYPE_MULTIPLE_CHOICE,
                'is_required' => false,
                'weight' => 0,
                'status' => Question::STATUS_ACTIVE,
            ]);

            $feedbackOptions = [
                'Tidak ada',
                'Ada - masalah driver',
                'Ada - masalah kendaraan',
                'Ada - masalah keselamatan',
                'Ada - lainnya',
            ];

            foreach ($feedbackOptions as $index => $option) {
                $feedback->options()->updateOrCreate(
                    ['sort_order' => $index + 1],
                    ['option_text' => $option],
                );
            }
            $feedback->options()->whereNotIn('sort_order', range(1, count($feedbackOptions)))->delete();

            Question::query()->updateOrCreate([
                'target_type' => Question::TARGET_FEEDBACK,
                'sort_order' => 16,
            ], [
                'question' => 'Mohon jelaskan kondisi yang perlu ditindaklanjuti.',
                'instruction' => 'Kolom ini akan muncul bila Anda melaporkan adanya masalah.',
                'placeholder' => 'Tuliskan kondisi atau keluhan Anda...',
                'rating_min_label' => null,
                'rating_max_label' => null,
                'indicator_category_id' => $this->indicatorCategory(Question::TARGET_FEEDBACK, Question::FEEDBACK_INDICATOR),
                'answer_type' => Question::TYPE_PARAGRAPH,
                'is_required' => false,
                'weight' => 0,
                'status' => Question::STATUS_ACTIVE,
            ]);
        });
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: int}>  $questions
     */
    private function createRatings(string $targetType, array $questions, int $startOrder): void
    {
        foreach ($questions as $offset => [$indicator, $question, $weight]) {
            $sortOrder = $startOrder + $offset;
            $rating = Question::query()->updateOrCreate([
                'target_type' => $targetType,
                'sort_order' => $sortOrder,
            ], [
                'question' => $question,
                'instruction' => null,
                'placeholder' => null,
                'rating_min_label' => 'Sangat Buruk',
                'rating_max_label' => 'Sangat Baik',
                'indicator_category_id' => $this->indicatorCategory($targetType, $indicator),
                'answer_type' => Question::TYPE_RATING,
                'is_required' => true,
                'weight' => $weight,
                'status' => Question::STATUS_ACTIVE,
            ]);
            $rating->options()->delete();
        }
    }

    private function indicatorCategory(string $targetType, string $name): int
    {
        $categoryId = IndicatorCategory::query()
            ->where('target_type', $targetType)
            ->where('name', $name)
            ->value('id');

        if (! $categoryId) {
            throw new RuntimeException("Kategori indikator {$name} untuk target {$targetType} belum tersedia.");
        }

        return (int) $categoryId;
    }
}
