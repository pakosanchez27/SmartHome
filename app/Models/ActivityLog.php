<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Registro inmutable de acciones sensibles (EP03-08, RG-10). */
class ActivityLog extends Model
{
    public const UPDATED_MEMBER_ROLE = 'household_member.role_updated';

    public const SUSPENDED_MEMBER = 'household_member.suspended';

    public const REACTIVATED_MEMBER = 'household_member.reactivated';

    public const REMOVED_MEMBER = 'household_member.removed';

    public const CREATED_INVITATION = 'household_invitation.created';

    public const RESENT_INVITATION = 'household_invitation.resent';

    public const REVOKED_INVITATION = 'household_invitation.revoked';

    public const ACCEPTED_INVITATION = 'household_invitation.accepted';

    public const REJECTED_INVITATION = 'household_invitation.rejected';

    public const CREATED_ACCOUNT = 'finance.account.created';

    public const UPDATED_ACCOUNT = 'finance.account.updated';

    public const CREATED_CATEGORY = 'finance.category.created';

    public const UPDATED_CATEGORY = 'finance.category.updated';

    public const RECORDED_TRANSACTION = 'finance.transaction.recorded';

    public const UPDATED_TRANSACTION = 'finance.transaction.updated';

    public const CANCELED_TRANSACTION = 'finance.transaction.canceled';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'datetime'];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** Etiqueta en es-MX para el feed de actividad (J04). */
    public function label(): string
    {
        return static::actionLabels()[$this->action] ?? 'Acción registrada';
    }

    /**
     * Acción => texto legible. Toda acción nueva debe registrarse aquí: el
     * respaldo de `label()` evita romper la página, pero la prueba
     * `ActivityLogLabelTest` exige que ninguna acción quede sin etiqueta.
     *
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            self::UPDATED_MEMBER_ROLE => 'Se actualizó un rol',
            self::SUSPENDED_MEMBER => 'Se desactivó un integrante',
            self::REACTIVATED_MEMBER => 'Se reactivó un integrante',
            self::REMOVED_MEMBER => 'Se retiró un integrante',
            self::CREATED_INVITATION => 'Se invitó a una persona',
            self::RESENT_INVITATION => 'Se reenvió una invitación',
            self::REVOKED_INVITATION => 'Se canceló una invitación',
            self::ACCEPTED_INVITATION => 'Se aceptó una invitación',
            self::REJECTED_INVITATION => 'Se rechazó una invitación',
            self::CREATED_ACCOUNT => 'Se creó una cuenta',
            self::UPDATED_ACCOUNT => 'Se actualizó una cuenta',
            self::CREATED_CATEGORY => 'Se creó una categoría',
            self::UPDATED_CATEGORY => 'Se actualizó una categoría',
            self::RECORDED_TRANSACTION => 'Se registró un movimiento',
            self::UPDATED_TRANSACTION => 'Se corrigió un movimiento',
            self::CANCELED_TRANSACTION => 'Se anuló un movimiento',
        ];
    }
}
