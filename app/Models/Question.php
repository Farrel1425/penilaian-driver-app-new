<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['question', 'instruction', 'placeholder', 'rating_min_label', 'rating_max_label', 'icon_path', 'indicator_category_id', 'target_type', 'answer_type', 'is_required', 'weight', 'sort_order', 'status'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public const TARGET_DRIVER = 'driver';

    public const TARGET_VEHICLE = 'vehicle';

    public const TARGET_FEEDBACK = 'feedback';

    public const VEHICLE_INDICATOR = 'Kendaraan';

    public const FEEDBACK_INDICATOR = 'Feedback/Keluhan';

    public static function targetLabel(string $targetType): string
    {
        return match ($targetType) {
            self::TARGET_DRIVER => 'Driver',
            self::TARGET_VEHICLE => 'Kendaraan',
            self::TARGET_FEEDBACK => 'Feedback/Keluhan',
            default => $targetType,
        };
    }

    public const TYPE_RATING = 'rating';

    public const TYPE_YES_NO = 'yes_no';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_CHECKBOX = 'checkbox';

    public const TYPE_SHORT_TEXT = 'short_text';

    public const TYPE_PARAGRAPH = 'paragraph';

    public const ANSWER_TYPES = [
        self::TYPE_RATING,
        self::TYPE_YES_NO,
        self::TYPE_MULTIPLE_CHOICE,
        self::TYPE_CHECKBOX,
        self::TYPE_SHORT_TEXT,
        self::TYPE_PARAGRAPH,
    ];

    public static function answerTypeLabel(string $answerType): string
    {
        return match ($answerType) {
            self::TYPE_RATING => 'Rating 1-5',
            self::TYPE_YES_NO => 'Ya / Tidak',
            self::TYPE_MULTIPLE_CHOICE => 'Pilihan Ganda',
            self::TYPE_CHECKBOX => 'Checkbox',
            self::TYPE_SHORT_TEXT => 'Jawaban Singkat',
            self::TYPE_PARAGRAPH => 'Paragraf',
            default => $answerType,
        };
    }

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $with = ['indicatorCategory'];

    public function indicatorCategory(): BelongsTo
    {
        return $this->belongsTo(IndicatorCategory::class);
    }

    public function getIndicatorAttribute(): ?string
    {
        return $this->indicatorCategory?->name;
    }

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
