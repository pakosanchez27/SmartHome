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
