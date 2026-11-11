<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Household;
use App\Models\User;

/**
 * EP04-08: categorías compartidas del hogar. Solo los roles operativos las
 * administran; `viewer` puede consultarlas pero no crearlas ni editarlas.
 */
class CategoryPolicy
{
    public function viewAny(User $user, Household $household): bool
    {
        return $user->roleIn($household) !== null;
    }

    public function create(User $user, Household $household): bool
    {
        return $this->operates($user, $household);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->operates($user, $category->household_id);
    }

    public function delete(User $user, Category $category): bool
    {
        return false;
    }

    private function operates(User $user, Household|int $household): bool
    {
        return in_array($user->roleIn($household), ['admin', 'member'], true);
    }
}
