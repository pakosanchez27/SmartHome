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
}
