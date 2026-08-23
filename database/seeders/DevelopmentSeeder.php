<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $branchUtama = Branch::query()->updateOrCreate(
            ['code' => 'DPS-001'],
            [
                'name' => 'Cabang Denpasar',
                'address' => 'Denpasar, Bali',
                'regency' => 'Kota Denpasar',
                'pic_name' => 'PIC Cabang Denpasar',
                'phone' => '0361-700001',
                'email' => 'denpasar@lais.test',
                'status' => Branch::STATUS_ACTIVE,
            ]
        );

        $branchKedua = Branch::query()->updateOrCreate(
            ['code' => 'GIA-001'],
            [
                'name' => 'Cabang Gianyar',
                'address' => 'Gianyar, Bali',
                'regency' => 'Kabupaten Gianyar',
                'pic_name' => 'PIC Cabang Gianyar',
                'phone' => '0361-700002',
                'email' => 'gianyar@lais.test',
                'status' => Branch::STATUS_ACTIVE,
            ]
        );

        if ($branchUtama->drivers()->count() === 0) {
            Driver::factory()->count(3)->for($branchUtama)->create();
        }

        if ($branchKedua->drivers()->count() === 0) {
            Driver::factory()->count(2)->for($branchKedua)->create();
        }

        if ($branchUtama->vehicles()->count() === 0) {
            Vehicle::factory()->count(2)->for($branchUtama)->create();
        }

        if ($branchKedua->vehicles()->count() === 0) {
            Vehicle::factory()->count(2)->for($branchKedua)->create();
        }

        $driverRating = Question::query()->updateOrCreate(
            ['question' => 'Bagaimana keramahan driver?'],
            [
                'target_type' => Question::TARGET_DRIVER,
                'indicator' => 'Sikap & Etika',
                'answer_type' => Question::TYPE_RATING,
                'is_required' => true,
                'weight' => 50,
                'sort_order' => 1,
                'status' => Question::STATUS_ACTIVE,
            ]
        );

        $vehicleRating = Question::query()->updateOrCreate(
            ['question' => 'Bagaimana kebersihan kendaraan?'],
            [
                'target_type' => Question::TARGET_VEHICLE,
                'indicator' => Question::VEHICLE_INDICATOR,
                'answer_type' => Question::TYPE_RATING,
                'is_required' => true,
                'weight' => 50,
                'sort_order' => 2,
                'status' => Question::STATUS_ACTIVE,
            ]
        );

        $yesNo = Question::query()->updateOrCreate(
            ['question' => 'Apakah driver mengemudi dengan aman?'],
            [
                'target_type' => Question::TARGET_DRIVER,
                'indicator' => 'Keselamatan Mengemudi',
                'answer_type' => Question::TYPE_YES_NO,
                'is_required' => true,
                'weight' => 50,
                'sort_order' => 3,
                'status' => Question::STATUS_ACTIVE,
            ]
        );

        $multipleChoice = Question::query()->updateOrCreate(
            ['question' => 'Bagian kendaraan mana yang perlu diperbaiki?'],
            [
                'target_type' => Question::TARGET_VEHICLE,
                'indicator' => Question::VEHICLE_INDICATOR,
                'answer_type' => Question::TYPE_MULTIPLE_CHOICE,
                'is_required' => false,
                'weight' => 50,
                'sort_order' => 4,
                'status' => Question::STATUS_ACTIVE,
            ]
        );

        foreach (['AC', 'Kursi', 'Kebersihan'] as $index => $option) {
            $multipleChoice->options()->firstOrCreate(
                ['option_text' => $option],
                ['sort_order' => $index + 1]
            );
        }

        if (Rating::query()->count() === 0) {
            $rating = Rating::query()->create([
                'branch_id' => $branchUtama->id,
                'vehicle_id' => $branchUtama->vehicles()->first()->id,
                'driver_id' => $branchUtama->drivers()->first()->id,
                'submitted_at' => now(),
            ]);

            $rating->answers()->createMany([
                ['question_id' => $driverRating->id, 'answer_value' => [5]],
                ['question_id' => $vehicleRating->id, 'answer_value' => [4]],
                ['question_id' => $yesNo->id, 'answer_value' => [1]],
            ]);
        }
    }
}
