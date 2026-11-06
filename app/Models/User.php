<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function households(): BelongsToMany
    {
        return $this->belongsToMany(Household::class)->withPivot(['role', 'deactivated_at', 'removed_at'])->withTimestamps();
    }

    /** Membresías vigentes: ni desactivadas ni retiradas (RG-01: los inactivos no operan). */
    public function activeHouseholds(): BelongsToMany
    {
        return $this->households()
            ->wherePivotNull('deactivated_at')
            ->wherePivotNull('removed_at');
    }

    /** Rol activo del usuario en un hogar, o null si no pertenece (o está desactivado/retirado). */
    public function roleIn(Household|int $household): ?string
    {
        $householdId = $household instanceof Household ? $household->id : $household;
        $pivot = $this->households()->firstWhere('household_id', $householdId)?->pivot;

        return $pivot !== null
            && $pivot->deactivated_at === null
            && $pivot->removed_at === null
            ? $pivot->role
            : null;
    }

    public function isAdminOf(Household|int $household): bool
    {
        return $this->roleIn($household) === 'admin';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
