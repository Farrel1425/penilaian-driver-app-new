<?php

namespace Database\Seeders;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Database\Seeder;

class IndicatorCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            Question::TARGET_DRIVER => [
                'Sikap & Etika',
                'Keselamatan Mengemudi',
                'Kepatuhan',
                'Ketepatan Waktu',
                'Komunikasi & Responsivitas',
                'Pelaksanaan Penugasan',
                'Penampilan',
            ],
            Question::TARGET_VEHICLE => [
                Question::VEHICLE_INDICATOR,
            ],
            Question::TARGET_FEEDBACK => [
                Question::FEEDBACK_INDICATOR,
            ],
        ];

        foreach ($categories as $targetType => $names) {
            foreach ($names as $index => $name) {
                IndicatorCategory::query()->updateOrCreate(
                    ['target_type' => $targetType, 'name' => $name],
                    [
                        'status' => IndicatorCategory::STATUS_ACTIVE,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}
