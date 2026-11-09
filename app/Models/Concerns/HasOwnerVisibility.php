<?php

namespace App\Models\Concerns;

use App\Enums\Visibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * EP03-07: contrato de visibilidad para recursos privados/compartidos (ADR-0002).
 *
 * Cómo usarlo en EP-04 y siguientes:
 * - La tabla incluye `owner_user_id` FK a users (NULL = recurso del hogar,
 *   sin titular) y `shared_at` timestamp NULL (NULL = privada; fecha = compartida).
 * - La consulta SIEMPRE lleva también household_id (RG-01): este trait añade
 *   el filtro de titularidad y visibilidad, no reemplaza el aislamiento por hogar.
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
        $ownerId = $this->getAttribute($this->getOwnerColumn());

        if ($ownerId === null) {
            return true; // recurso del hogar
        }

        return (int) $ownerId === (int) $viewer->id || $this->isShared();
    }

    /** Limita una consulta (ya filtrada por household_id) a lo que el usuario ve. */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        $owner = $query->getModel()->getOwnerColumn();
        $shared = $query->getModel()->qualifyColumn('shared_at');

        return $query->where(function (Builder $q) use ($viewer, $owner, $shared): void {
            $q->whereNull($owner)
                ->orWhere($owner, $viewer->id)
                ->orWhereNotNull($shared);
        });
    }
}
