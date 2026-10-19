<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class AuditLogger
{
    public static function record(
        string $action,
        ?Model $subject = null,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
    ): void {
        $request = request();
        $userAgent = $request?->userAgent();

        AuditLog::create([
            'actor_user_id' => $actor?->getKey() ?? auth()->id(),
            'action' => $action,
            'entity_type' => $subject ? $subject::class : 'system',
            'entity_id' => $subject?->getKey(),
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request?->ip(),
            'user_agent' => $userAgent ? Str::limit($userAgent, 1000, '') : null,
            'request_id' => $request?->header('X-Request-Id'),
            'created_at' => now(),
        ]);
    }
}
