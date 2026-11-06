<?php

namespace App\Models;

use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Household extends Model
{
    /** @use HasFactory<HouseholdFactory> */
    use HasFactory;

    protected $fillable = ['name', 'currency', 'timezone', 'cutoff_day', 'budget_carryover'];

    protected $casts = ['budget_carryover' => 'boolean'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role', 'deactivated_at'])->withTimestamps();
    }
}
