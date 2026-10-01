<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function log(?User $user, string $action, Model|string $subject, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): AuditLog
    {
        $request = request();

        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $subject instanceof Model ? $subject->getMorphClass() : $subject,
            'auditable_id' => $subject instanceof Model ? $subject->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'description' => $description,
        ]);
    }
}
