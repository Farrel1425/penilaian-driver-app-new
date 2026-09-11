<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Development', 'password' => Hash::make('password')]
        );

        $this->call([
            BranchSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            IndicatorCategorySeeder::class,
            ClientQuestionSeeder::class,
        ]);
    }
}
