<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'recruitment_period_id',
    'category',
    'work_type',
    'title',
    'description',
    'qualification',
    'compensation',
    'quota',
    'sort_order',
    'status',
])]
class JobVacancy extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_OPEN => 'Dibuka',
        self::STATUS_CLOSED => 'Ditutup',
    ];

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPeriod::class, 'recruitment_period_id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'job_vacancy_branch');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_OPEN)
            ->whereHas('period', fn (Builder $query) => $query->active())
            ->whereHas('branches', fn (Builder $query) => $query->active());
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function placementLabel(): string
    {
        return $this->branches->pluck('name')->join(', ');
    }
}
