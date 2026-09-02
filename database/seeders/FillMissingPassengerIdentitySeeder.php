<?php

namespace Database\Seeders;

use App\Models\Rating;
use Illuminate\Database\Seeder;

class FillMissingPassengerIdentitySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Made Putra', 'Kadek Sari', 'Komang Adi', 'Putu Lestari', 'Ketut Wijaya'];
        $units = ['Kantor Pusat', 'Unit Operasional', 'Unit Layanan Nasabah', 'Unit Administrasi', 'Unit Keuangan'];

        Rating::query()->orderBy('id')->each(function (Rating $rating, int $index) use ($names, $units): void {
            $data = [];

            if (blank($rating->passenger_name)) {
                $data['passenger_name'] = $names[$index % count($names)];
            }

            if (blank($rating->passenger_unit)) {
                $data['passenger_unit'] = $units[$index % count($units)];
            }

            if ($data !== []) {
                $rating->update($data);
            }
        });
    }
}