<?php

namespace App\Models\Concerns;

use App\Enums\Visibility;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * EP03-07: contrato de visibilidad para recursos privados/compartidos (ADR-0002).
 *
 * Cómo usarlo en EP-04 y siguientes:
 * - La tabla incluye `owner_user_id` FK a users (NULL = recurso del hogar,
 *   sin titular) y `shared_at` timestamp NULL (NULL = privada; fecha = compartida).
 * - El alcance exige el household_id y valida una membresía activa (RG-01);
 *   no existe una variante sin hogar para evitar filtraciones por omisión.
 *
 * Reglas:
 * - Recurso sin titular → lo ve cualquiera con rol activo en el hogar.
 * - Recurso del titular → solo el titular lo ve, o el hogar si está compartido.
 * - Cambiar de visibilidad gestiona `shared_at`; nunca se borra el registro
 *   ni su histórico (RG-10). La vigencia actual rige en reportes y agregados.
 */
trait HasOwnerVisibility
{
    /** Nombre de la columna FK del titular. EP-04+: owner_user_id. */
    public function getOwnerColumn(): string
    {
        return 'owner_user_id';
    }

    /** Nombre de la columna FK del hogar. EP-04+: household_id. */
    public function getHouseholdColumn(): string
    {
        return 'household_id';
    }

    /** Momento en que el titular compartió el recurso con el hogar (null = privada). */
    public function isShared(): bool
    {
        return $this->getAttribute('shared_at') !== null;
    }

    public function visibilityStatus(): Visibility
    {
        return $this->isShared() ? Visibility::Shared : Visibility::Private;
    }

    public function isVisibleTo(User $viewer): bool
    {
        $householdId = $this->getAttribute($this->getHouseholdColumn());

        if ($householdId === null || $viewer->roleIn((int) $householdId) === null) {
            return false;
        }

        $ownerId = $this->getAttribute($this->getOwnerColumn());

        if ($ownerId === null) {
            return true; // recurso del hogar
        }

        return (int) $ownerId === (int) $viewer->id || $this->isShared();
    }

    /**
     * Limita la consulta al hogar indicado y a los recursos que el integrante
     * activo puede ver. Requerir el hogar evita que un listado o agregado
     * accidentalmente mezcle recursos entre hogares (RG-01).
     */
    public function scopeVisibleTo(Builder $query, User $viewer, Household|int $household): Builder
    {
        $householdId = $household instanceof Household ? $household->id : $household;
        $model = $query->getModel();
        $householdColumn = $model->qualifyColumn($model->getHouseholdColumn());

        if ($viewer->roleIn($householdId) === null) {
            return $query->whereRaw('1 = 0');
        }

        $owner = $query->getModel()->getOwnerColumn();
        $shared = $model->qualifyColumn('shared_at');

        return $query
            ->where($householdColumn, $householdId)
            ->where(function (Builder $q) use ($viewer, $owner, $shared): void {
                $q->whereNull($owner)
                    ->orWhere($owner, $viewer->id)
                    ->orWhereNotNull($shared);
            });
    }
}
