<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_name',
    'contact_name',
    'whatsapp',
    'email',
    'service',
    'estimated_need',
    'contract_duration',
    'notes',
    'status',
])]
class PartnershipInquiry extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_NEW => 'Baru',
        self::STATUS_IN_PROGRESS => 'Diproses',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
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
