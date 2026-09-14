<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['key', 'value'])]
class SystemSetting extends Model
{
    private const CACHE_KEY = 'system-settings.values';

    public static function values(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => self::query()->pluck('value', 'key')->all());
    }

    public static function value(string $key, ?string $default = null): ?string
    {
        return self::values()[$key] ?? $default;
    }

    public static function logoUrl(string $fallback = 'images/bds/bds-logo.png'): string
    {
        $logo = self::value('logo');

        if (blank($logo)) {
            return asset($fallback);
        }

        return Str::startsWith($logo, ['http://', 'https://', '/'])
            ? $logo
            : Storage::disk('public')->url($logo);
    }

    public static function systemName(): string
    {
        return trim((string) self::value('system_name', 'Aplikasi Penilaian Driver'));
    }

    public static function copyrightText(): ?string
    {
        $values = self::values();

        if (! array_key_exists('copyright_text', $values)) {
            return '© '.now()->year.'. Seluruh hak dilindungi.';
        }

        $copyright = trim((string) $values['copyright_text']);

        return $copyright !== '' ? $copyright : null;
    }

    public static function supportContact(): ?string
    {
        $contact = trim((string) self::value('support_contact'));

        return $contact !== '' ? $contact : null;
    }

    public static function supportContactUrl(): ?string
    {
        $contact = self::supportContact();

        if ($contact === null) {
            return null;
        }

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:'.$contact;
        }

        if (Str::startsWith($contact, ['https://', 'http://', 'mailto:', 'tel:'])) {
            return $contact;
        }

        if (preg_match('/^\+?[0-9\s().-]{7,}$/', $contact) !== 1) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $contact);

        if (Str::startsWith($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return $number !== '' ? 'https://wa.me/'.$number : null;
    }

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }
}
