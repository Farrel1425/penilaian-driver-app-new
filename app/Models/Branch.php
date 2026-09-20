<?php

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'address', 'regency', 'pic_name', 'phone', 'email', 'status'])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const BALI_REGENCIES = [
        'Kabupaten Badung',
        'Kabupaten Bangli',
        'Kabupaten Buleleng',
        'Kabupaten Gianyar',
        'Kabupaten Jembrana',
        'Kabupaten Karangasem',
        'Kabupaten Klungkung',
        'Kabupaten Tabanan',
        'Kota Denpasar',
    ];

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function driverAttendances(): HasMany
    {
        return $this->hasMany(DriverAttendance::class);
    }

    public function jobVacancies(): BelongsToMany
    {
        return $this->belongsToMany(JobVacancy::class, 'job_vacancy_branch');
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
