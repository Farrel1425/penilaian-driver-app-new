<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'branch_id',
    'driver_id',
    'entered_by',
    'period',
    'present_days',
    'sick_days',
    'permitted_days',
    'absent_days',
])]
class DriverAttendance extends Model
{
    use HasFactory;

    public const PRESENT_WEIGHT = 100;

    public const SICK_WEIGHT = 75;

    public const PERMITTED_WEIGHT = 50;

    public const ABSENT_WEIGHT = 0;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function totalDays(): int
    {
        return $this->present_days + $this->sick_days + $this->permitted_days + $this->absent_days;
    }

    public function score(): ?float
    {
        $total = $this->totalDays();

        if ($total === 0) {
            return null;
        }

        $points = ($this->present_days * self::PRESENT_WEIGHT)
            + ($this->sick_days * self::SICK_WEIGHT)
            + ($this->permitted_days * self::PERMITTED_WEIGHT)
            + ($this->absent_days * self::ABSENT_WEIGHT);

        return round($points / $total, 2);
    }

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'present_days' => 'integer',
            'sick_days' => 'integer',
            'permitted_days' => 'integer',
            'absent_days' => 'integer',
        ];
    }
}
