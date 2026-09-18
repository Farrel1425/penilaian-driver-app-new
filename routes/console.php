<?php

use App\Models\Driver;
use App\Models\Question;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('images:prune-orphans {--delete : Hapus file yatim yang ditemukan}', function (): int {
    $directories = [
        'drivers',
        'driver-sims',
        'profiles',
        'vehicles/exterior',
        'vehicles/interior',
        'question-icons',
        'settings',
    ];

    $referenced = collect()
        ->merge(Driver::query()->pluck('photo'))
        ->merge(Driver::query()->pluck('sim_photo'))
        ->merge(Vehicle::query()->pluck('photo'))
        ->merge(Vehicle::query()->pluck('interior_photo'))
        ->merge(User::query()->pluck('photo'))
        ->merge(Question::query()->pluck('icon_path'))
        ->push(SystemSetting::value('logo'))
        ->filter(fn ($path) => filled($path) && ! Str::startsWith($path, ['http://', 'https://', '/']))
        ->unique();

    $orphans = collect($directories)
        ->flatMap(fn (string $directory) => Storage::disk('public')->allFiles($directory))
        ->diff($referenced)
        ->values();

    if ($orphans->isEmpty()) {
        $this->info('Tidak ada file gambar yatim pada folder aplikasi.');

        return Command::SUCCESS;
    }

    $this->table(['File gambar yatim'], $orphans->map(fn (string $path) => [$path]));

    if (! $this->option('delete')) {
        $this->warn("Ditemukan {$orphans->count()} file. Jalankan kembali dengan --delete untuk menghapusnya.");

        return Command::SUCCESS;
    }

    Storage::disk('public')->delete($orphans->all());
    $this->info("{$orphans->count()} file gambar yatim berhasil dihapus. Database tidak diubah.");

    return Command::SUCCESS;
})->purpose('Audit atau hapus gambar aplikasi yang tidak lagi direferensikan database');
