<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EP03-04: invitación a un hogar.
 *
 * Estados: pending → accepted / rejected / revoked ("cancelada").
 * Vence 7 días después de emitirse (delineado en J02, Figma).
 */
class HouseholdInvitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REVOKED = 'revoked';

    public const EXPIRES_IN_DAYS = 7;

    protected $fillable = [
        'household_id',
        'invited_by',
        'accepted_by',
        'email',
        'role',
        'token_hash',
        'status',
        'message',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /** La invitación puede usarse: pendiente y dentro de su vigencia. */
    public function isUsable(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at->isFuture();
    }

    /** Genera el par [token crudo para el enlace, hash para la base de datos]. */
    public static function issueToken(): array
    {
        $raw = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return [$raw, hash('sha256', $raw)];
    }

    /** Etiqueta legible del rol (es-MX). */
    public function roleLabel(): string
    {
        return self::roleLabelFor($this->role);
    }

    /** Etiqueta legible por llave de rol ('admin'|'member'|'viewer'). */
    public static function roleLabelFor(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrador(a)',
            'member' => 'Integrante',
            'viewer' => 'Consulta',
            default => ucfirst($role),
        };
    }
}
