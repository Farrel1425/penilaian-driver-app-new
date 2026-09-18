<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicImageStorage
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://', '/'])) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @param array<int, string|null> $paths */
    public function deleteMany(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $this->delete($path);
        }
    }
}
