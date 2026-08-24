<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    /** @param array<string, mixed> $metadata */
    public function log(?User $user, string $module, string $action, string $description, ?Request $request = null, array $metadata = []): void
    {
        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata ?: null,
        ]);
    }
}
