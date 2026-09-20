<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'job_vacancy_id',
    'branch_id',
    'full_name',
    'nik',
    'nik_hash',
    'whatsapp',
    'email',
    'domicile',
    'experience',
    'document_path',
    'document_original_name',
    'status',
    'consent_at',
])]
class JobApplication extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_NEW => 'Baru',
        self::STATUS_REVIEWED => 'Ditinjau',
        self::STATUS_CONTACTED => 'Dihubungi',
        self::STATUS_ACCEPTED => 'Diterima',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'consent_at' => 'datetime',
        ];
    }

    public static function nikHash(string $nik): string
    {
        return hash_hmac('sha256', $nik, (string) config('app.key'));
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function maskedNik(): string
    {
        return substr($this->nik, 0, 4).'********'.substr($this->nik, -4);
    }

    public function whatsappNumber(): string
    {
        $number = preg_replace('/\D+/', '', $this->whatsapp) ?? '';

        if (str_starts_with($number, '0')) {
            return '62'.substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            return '62'.$number;
        }

        return $number;
    }
}
