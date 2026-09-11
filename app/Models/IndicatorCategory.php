<?php

namespace App\Models;

use Database\Factories\IndicatorCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'target_type', 'status', 'sort_order'])]
class IndicatorCategory extends Model
{
    /** @use HasFactory<IndicatorCategoryFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('target_type')->orderBy('sort_order')->orderBy('name');
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }
}
