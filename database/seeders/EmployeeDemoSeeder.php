<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\EmployeeCategory;
use App\Models\Question;
use App\Models\Rating;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EmployeeDemoSeeder extends Seeder
{
    private const RECORDS_PER_BRANCH = 10;

    public function run(): void
    {
        $categories = $this->categories();
        $branches = $this->branches();
        $driverPhotos = collect(Storage::disk('public')->files('drivers'));
        $simPhotos = collect(Storage::disk('public')->files('driver-sims'));
        $vehiclePhotos = collect(Storage::disk('public')->files('vehicles/exterior'));
        $interiorPhotos = collect(Storage::disk('public')->files('vehicles/interior'));
        $questions = $this->questions();

        $branches->each(function (Branch $branch) use ($categories, $driverPhotos, $simPhotos, $vehiclePhotos, $interiorPhotos, $questions): void {
            $this->employees($branch, $categories, $driverPhotos, $simPhotos);
            $this->vehicles($branch, $vehiclePhotos, $interiorPhotos);
            $this->ratings($branch, $questions);
        });
    }

    /** @return Collection<string, EmployeeCategory> */
    private function categories(): Collection
    {
        return collect([
            ['name' => 'Driver', 'requires_sim' => true],
            ['name' => 'Customer Service', 'requires_sim' => false],
            ['name' => 'Satpam', 'requires_sim' => false],
            ['name' => 'Administrasi', 'requires_sim' => false],
        ])->mapWithKeys(function (array $category): array {
            $record = EmployeeCategory::query()->firstOrCreate(
                ['name' => $category['name']],
                ['requires_sim' => $category['requires_sim'], 'status' => EmployeeCategory::STATUS_ACTIVE],
            );

            return [$record->name => $record];
        });
    }

    /** @return Collection<int, Branch> */
    private function branches(): Collection
    {
        if (Branch::query()->exists()) {
            return Branch::query()->active()->orderBy('name')->get();
        }

        return collect([
            ['code' => 'DPS-001', 'name' => 'Cabang Denpasar', 'regency' => 'Kota Denpasar'],
            ['code' => 'BDG-001', 'name' => 'Cabang Badung', 'regency' => 'Kabupaten Badung'],
            ['code' => 'GIA-001', 'name' => 'Cabang Gianyar', 'regency' => 'Kabupaten Gianyar'],
        ])->map(function (array $branch): Branch {
            return Branch::query()->firstOrCreate(
                ['code' => $branch['code']],
                [
                    ...$branch,
                    'address' => "Jl. {$branch['name']}, Bali",
                    'pic_name' => "PIC {$branch['name']}",
                    'phone' => '0361-'.fake()->unique()->numerify('#######'),
                    'email' => strtolower(str_replace(' ', '.', $branch['code'])).'@demo.local',
                    'status' => Branch::STATUS_ACTIVE,
                ],
            );
        });
    }

    private function employees(Branch $branch, Collection $categories, Collection $photos, Collection $simPhotos): void
    {
        $missing = max(0, self::RECORDS_PER_BRANCH - $branch->drivers()->count());

        if ($missing === 0) {
            return;
        }

        foreach (range(0, $missing - 1) as $offset) {
            $category = $categories->get(['Driver', 'Driver', 'Driver', 'Driver', 'Customer Service', 'Customer Service', 'Satpam', 'Satpam', 'Administrasi', 'Administrasi'][$offset % 10]);
            $isDriver = $category?->requires_sim;

            Driver::factory()->for($branch)->create([
                'employee_category_id' => $category?->id,
                'photo' => $photos->isNotEmpty() ? $photos->get($offset % $photos->count()) : null,
                'sim_photo' => $isDriver && $simPhotos->isNotEmpty() ? $simPhotos->get($offset % $simPhotos->count()) : null,
                'sim_number' => $isDriver ? "SIM-A-{$branch->id}-".str_pad((string) ($offset + 1), 3, '0', STR_PAD_LEFT) : null,
                'sim_type' => $isDriver ? 'A' : null,
                'sim_expired_at' => $isDriver ? now()->addYears(3)->addDays($offset)->toDateString() : null,
            ]);
        }
    }

    private function vehicles(Branch $branch, Collection $photos, Collection $interiorPhotos): void
    {
        $missing = max(0, self::RECORDS_PER_BRANCH - $branch->vehicles()->count());

        if ($missing === 0) {
            return;
        }

        foreach (range(0, $missing - 1) as $offset) {
            Vehicle::factory()->for($branch)->create([
                'photo' => $photos->isNotEmpty() ? $photos->get($offset % $photos->count()) : null,
                'interior_photo' => $interiorPhotos->isNotEmpty() ? $interiorPhotos->get($offset % $interiorPhotos->count()) : null,
            ]);
        }
    }

    /** @return Collection<int, Question> */
    private function questions(): Collection
    {
        return collect([
            [Question::TARGET_DRIVER, 'Sikap & Etika', 'Apakah driver bersikap ramah dan profesional?', 1],
            [Question::TARGET_DRIVER, 'Keselamatan Mengemudi', 'Apakah driver mengemudi dengan aman?', 2],
            [Question::TARGET_VEHICLE, 'Kebersihan Kendaraan', 'Apakah kendaraan bersih dan nyaman digunakan?', 3],
            [Question::TARGET_VEHICLE, 'Kondisi Kendaraan', 'Apakah kendaraan berfungsi dengan baik?', 4],
        ])->map(function (array $question): Question {
            [$target, $indicator, $text, $order] = $question;

            return Question::query()->firstOrCreate(
                ['question' => $text, 'target_type' => $target],
                [
                    'instruction' => null,
                    'rating_min_label' => 'Sangat Buruk',
                    'rating_max_label' => 'Sangat Baik',
                    'indicator' => $indicator,
                    'answer_type' => Question::TYPE_RATING,
                    'is_required' => true,
                    'weight' => 25,
                    'sort_order' => $order,
                    'status' => Question::STATUS_ACTIVE,
                ],
            );
        });
    }

    private function ratings(Branch $branch, Collection $questions): void
    {
        $missing = max(0, self::RECORDS_PER_BRANCH - $branch->ratings()->count());
        $drivers = $branch->drivers()->active()->eligibleForAssessment()->get();
        $vehicles = $branch->vehicles()->active()->get();

        if ($drivers->isEmpty() || $vehicles->isEmpty()) {
            return;
        }

        if ($missing === 0) {
            return;
        }

        foreach (range(0, $missing - 1) as $offset) {
            $rating = Rating::query()->create([
                'branch_id' => $branch->id,
                'driver_id' => $drivers[$offset % $drivers->count()]->id,
                'vehicle_id' => $vehicles[$offset % $vehicles->count()]->id,
                'passenger_name' => fake()->name(),
                'passenger_unit' => fake()->randomElement(['Kantor Pusat', 'Operasional', 'Layanan Nasabah']),
                'submitted_at' => now()->subDays($offset * 2)->setTime(8 + ($offset % 9), 15),
            ]);

            $rating->answers()->createMany($questions->map(function (Question $question) use ($offset): array {
                return [
                    'question_id' => $question->id,
                    'answer_value' => [3 + (($offset + $question->sort_order) % 3)],
                ];
            })->all());
        }
    }
}
