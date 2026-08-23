<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['question', 'instruction', 'placeholder', 'rating_min_label', 'rating_max_label', 'icon_path', 'indicator', 'target_type', 'answer_type', 'is_required', 'weight', 'sort_order', 'status'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public const TARGET_DRIVER = 'driver';

    public const TARGET_VEHICLE = 'vehicle';

    public const VEHICLE_INDICATOR = 'Kendaraan';

    public const TYPE_RATING = 'rating';

    public const TYPE_YES_NO = 'yes_no';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_CHECKBOX = 'checkbox';

    public const TYPE_SHORT_TEXT = 'short_text';

    public const TYPE_PARAGRAPH = 'paragraph';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function ratingAnswers(): HasMany
    {
        return $this->hasMany(RatingAnswer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'weight' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
