<?php

namespace Database\Seeders;

use App\Models\Branch;
use Database\Seeders\Support\SeederCsv;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SeederCsv::rows('branches.csv') as $row) {
            Branch::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name']],
            );
        }
    }
}
