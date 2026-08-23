<?php

namespace App\Models;

use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'branch_id',
    'police_number',
    'brand',
    'model',
    'year',
    'color',
    'chassis_number',
    'engine_number',
    'fuel_type',
    'transmission',
    'passenger_capacity',
    'acquisition_date',
    'acquisition_source',
    'ownership_type',
    'contract_number',
    'contract_expired_at',
    'stnk_expired_at',
    'kir_expired_at',
    'description',
    'photo',
    'interior_photo',
    'status',
    'qr_token',
])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const OWNERSHIP_COMPANY = 'owned';

    public const OWNERSHIP_RENTAL = 'rental';

    public const FUEL_TYPES = [
        'gasoline' => 'Bensin',
        'diesel' => 'Diesel',
        'electric' => 'Listrik',
        'hybrid' => 'Hybrid',
        'gas' => 'Gas',
    ];

    public const TRANSMISSION_TYPES = [
        'manual' => 'Manual',
        'automatic' => 'Otomatis',
        'cvt' => 'CVT',
        'other' => 'Lainnya',
    ];

    public const ACQUISITION_SOURCES = [
        'purchase' => 'Pembelian',
        'leasing' => 'Leasing / Sewa',
        'grant' => 'Hibah',
        'internal_transfer' => 'Transfer Internal',
        'other' => 'Lainnya',
    ];

    public const OWNERSHIP_TYPES = [
        self::OWNERSHIP_COMPANY => 'Milik PT',
        self::OWNERSHIP_RENTAL => 'Leasing (Sewa)',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle): void {
            if (! $vehicle->qr_token) {
                $vehicle->qr_token = Str::random(40);
            }
        });
    }

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
            'year' => 'integer',
            'passenger_capacity' => 'integer',
            'acquisition_date' => 'date',
            'contract_expired_at' => 'date',
            'stnk_expired_at' => 'date',
            'kir_expired_at' => 'date',
        ];
    }
}
