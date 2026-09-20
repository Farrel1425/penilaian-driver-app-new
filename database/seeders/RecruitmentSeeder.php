<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecruitmentSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds = Branch::query()
            ->active()
            ->orderBy('id')
            ->pluck('id');

        if ($branchIds->isEmpty()) {
            $this->command?->warn('RecruitmentSeeder dilewati karena belum ada cabang aktif.');

            return;
        }

        DB::transaction(function () use ($branchIds): void {
            RecruitmentPeriod::query()->update(['is_active' => false]);

            $period = RecruitmentPeriod::query()->updateOrCreate(
                ['name' => 'Periode Recruitment September - Desember 2026'],
                [
                    'starts_at' => '2026-09-01',
                    'ends_at' => '2026-12-31',
                    'is_active' => true,
                ],
            );

            foreach ($this->vacancies() as $vacancyData) {
                $vacancy = JobVacancy::query()->updateOrCreate(
                    [
                        'recruitment_period_id' => $period->id,
                        'title' => $vacancyData['title'],
                    ],
                    $vacancyData,
                );

                $vacancy->branches()->sync($branchIds);
            }
        });
    }

    /**
     * @return list<array{
     *     category: string,
     *     work_type: string,
     *     title: string,
     *     description: string,
     *     qualification: string,
     *     compensation: string,
     *     quota: int,
     *     sort_order: int,
     *     status: string
     * }>
     */
    private function vacancies(): array
    {
        return [
            [
                'category' => 'Security & Garda',
                'work_type' => 'PKWTT 1 Tahun',
                'title' => 'Satpam / Security Officer',
                'description' => 'Bertanggung jawab atas pengamanan operasional perbankan, pengawalan kas, ketertiban nasabah di banking hall, serta inspeksi aset gedung.',
                'qualification' => 'Wajib Ijazah Gada Pratama & KTA Aktif',
                'compensation' => 'Gaji UMK + Tunjangan Khusus + BPJS',
                'quota' => 12,
                'sort_order' => 1,
                'status' => JobVacancy::STATUS_OPEN,
            ],
            [
                'category' => 'Armada & Driver',
                'work_type' => 'Penuh Waktu',
                'title' => 'Driver Operasional Bank',
                'description' => 'Mengemudikan kendaraan dinas perbankan, mobil pengantar kas, serta mendukung agenda perjalanan kedinasan pejabat instansi mitra.',
                'qualification' => 'Wajib SIM A / B1 Aktif, Rekam Jejak Aman',
                'compensation' => 'Gaji UMK + Uang Perjalanan + Lembur',
                'quota' => 8,
                'sort_order' => 2,
                'status' => JobVacancy::STATUS_OPEN,
            ],
            [
                'category' => 'Sanitasi Gedung',
                'work_type' => 'Shift Terjadwal',
                'title' => 'Cleaning Service & Fasilitas',
                'description' => 'Menjalankan pemeliharaan higienitas kantor kas, banking hall, sterilisasi area kerja pimpinan, dan manajemen tata letak sarana kebersihan.',
                'qualification' => 'Paham Pengoperasian Mesin Polisher & K3',
                'compensation' => 'Gaji Standar UMK + Seragam APD Lengkap',
                'quota' => 15,
                'sort_order' => 3,
                'status' => JobVacancy::STATUS_OPEN,
            ],
            [
                'category' => 'Teknik Pendingin AC',
                'work_type' => 'Mobile Service',
                'title' => 'Teknisi Pendingin (AC Gedung)',
                'description' => 'Pemeliharaan preventif dan perbaikan unit AC Split, Cassette, serta pendingin ruang data center (DRC) perbankan agar suhu beroperasi stabil 24/7.',
                'qualification' => 'SMK Teknik Pendingin / Listrik / BNSP',
                'compensation' => 'Gaji Pokok + Uang Perawatan + Insentif',
                'quota' => 4,
                'sort_order' => 4,
                'status' => JobVacancy::STATUS_OPEN,
            ],
            [
                'category' => 'Administrasi & Arsip',
                'work_type' => 'Regular Office',
                'title' => 'Staf Administrasi & Operator Arsip',
                'description' => 'Pengelolaan berkas kontrak kemitraan, penataan dokumen warkat bank, administrasi invoice operasional, serta verifikasi laporan logistik.',
                'qualification' => 'Min. D3/S1, Mahir Excel & Tata Kelola Berkas',
                'compensation' => 'Gaji Kompetitif Sesuai Pengalaman + BPJS',
                'quota' => 3,
                'sort_order' => 5,
                'status' => JobVacancy::STATUS_OPEN,
            ],
            [
                'category' => 'Logistik & Cetak Bank',
                'work_type' => 'Penuh Waktu',
                'title' => 'Petugas Logistik ATK & Percetakan',
                'description' => 'Menerima, menghitung stok, dan mengemas continuous form perbankan, buku tabungan, slip teller, serta mendistribusikan ATK ke kantor cabang.',
                'qualification' => 'SMA/SMK, Teliti, Stamina Fisik Baik',
                'compensation' => 'Gaji UMK Denpasar + Tunjangan Kerajinan',
                'quota' => 5,
                'sort_order' => 6,
                'status' => JobVacancy::STATUS_OPEN,
            ],
        ];
    }
}
