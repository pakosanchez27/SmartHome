<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Punto único para escribir auditoría. No registra secretos, tokens, correos,
 * importes ni contenido libre: solo actor, entidad, acción, fecha y origen.
 */
class ActivityLogger
{
    /** @param array<string, bool|float|int|string|null> $metadata */
    public function log(Household|int $household, ?User $actor, string $action, Model $subject, array $metadata = [], ?string $origin = null): ActivityLog
    {
        return ActivityLog::create([
            'household_id' => $household instanceof Household ? $household->id : $household,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'origin' => $origin ?? request()?->route()?->getName(),
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
