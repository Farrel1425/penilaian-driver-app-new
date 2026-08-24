<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Driver;
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

        $this->call(ClientQuestionSeeder::class);
    }
}
