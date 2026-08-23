<?php

namespace App\Models;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'full_name',
    'nickname',
    'birth_place',
    'birth_date',
    'gender',
    'address',
    'phone',
    'email',
    'marital_status',
    'photo',
    'sim_number',
    'sim_type',
    'sim_expired_at',
    'sim_photo',
    'join_date',
    'status',
])]
class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const MARITAL_STATUSES = [
        'single' => 'Belum Menikah',
        'married' => 'Menikah',
        'divorced' => 'Cerai Hidup',
        'widowed' => 'Cerai Mati',
    ];

    public const SIM_TYPES = [
        'A' => 'SIM A',
        'A Umum' => 'SIM A Umum',
        'BI' => 'SIM BI',
        'BI Umum' => 'SIM BI Umum',
        'BII' => 'SIM BII',
        'BII Umum' => 'SIM BII Umum',
        'C' => 'SIM C',
        'CI' => 'SIM CI',
        'CII' => 'SIM CII',
        'D' => 'SIM D',
        'DI' => 'SIM DI',
        'DII' => 'SIM DII',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'sim_expired_at' => 'date',
            'join_date' => 'date',
        ];
    }
}
