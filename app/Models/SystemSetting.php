<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }
}
