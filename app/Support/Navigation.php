<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Navegación del shell (EP-02).
 *
 * Fuente visual: Figma "02 — Components" → Sidebar specimen y Bottom nav.
 * Un ítem se habilita automáticamente cuando su ruta existe; así el menú
 * refleja el avance real de las épicas y nunca muestra enlaces muertos.
 */
final class Navigation
{
    /**
     * Navegación principal del sidebar.
     *
     * @return array<int, array{label: string, icon: string, route: ?string, active: string, epic: string}>
     */
    public static function main(): array
    {
        return [
            ['label' => 'Inicio', 'icon' => 'house', 'route' => 'home', 'active' => 'home', 'epic' => 'EP-16'],
            ['label' => 'Finanzas', 'icon' => 'wallet', 'route' => 'finance.index', 'active' => 'finance.*', 'epic' => 'EP-04'],
            ['label' => 'Alimentación', 'icon' => 'utensils', 'route' => 'meals.index', 'active' => 'meals.*', 'epic' => 'EP-09'],
            ['label' => 'Despensa', 'icon' => 'package', 'route' => 'pantry.index', 'active' => 'pantry.*', 'epic' => 'EP-10'],
            ['label' => 'Compras', 'icon' => 'shopping-cart', 'route' => 'shopping.index', 'active' => 'shopping.*', 'epic' => 'EP-12'],
            ['label' => 'Planes', 'icon' => 'calendar-days', 'route' => 'plans.index', 'active' => 'plans.*', 'epic' => 'EP-14'],
            ['label' => 'Necesidades y metas', 'icon' => 'target', 'route' => 'wishes.index', 'active' => 'wishes.*', 'epic' => 'EP-15'],
            ['label' => 'Asistente IA', 'icon' => 'sparkles', 'route' => 'ai.index', 'active' => 'ai.*', 'epic' => 'EP-17'],
            ['label' => 'Mi hogar', 'icon' => 'users', 'route' => 'household.index', 'active' => 'household.*', 'epic' => 'EP-03'],
            ['label' => 'Configuración', 'icon' => 'settings', 'route' => 'settings.index', 'active' => 'settings.*', 'epic' => 'EP-19'],
        ];
    }

    /**
     * Barra inferior móvil: Inicio / Finanzas / Compras / Alimentación / Más.
     *
     * @return array<int, array{label: string, icon: string, route: ?string, active: string, epic: string}>
     */
    public static function mobile(): array
    {
        return [
            ['label' => 'Inicio', 'icon' => 'house', 'route' => 'home', 'active' => 'home', 'epic' => 'EP-16'],
            ['label' => 'Finanzas', 'icon' => 'wallet', 'route' => 'finance.index', 'active' => 'finance.*', 'epic' => 'EP-04'],
            ['label' => 'Compras', 'icon' => 'shopping-cart', 'route' => 'shopping.index', 'active' => 'shopping.*', 'epic' => 'EP-12'],
            ['label' => 'Alimentación', 'icon' => 'utensils', 'route' => 'meals.index', 'active' => 'meals.*', 'epic' => 'EP-09'],
        ];
    }

    /**
     * Ítems secundarios dentro de la hoja "Más" en móvil.
     *
     * @return array<int, array{label: string, icon: string, route: ?string, active: string, epic: string}>
     */
    public static function more(): array
    {
        return [
            ['label' => 'Despensa', 'icon' => 'package', 'route' => 'pantry.index', 'active' => 'pantry.*', 'epic' => 'EP-10'],
            ['label' => 'Planes', 'icon' => 'calendar-days', 'route' => 'plans.index', 'active' => 'plans.*', 'epic' => 'EP-14'],
            ['label' => 'Necesidades y metas', 'icon' => 'target', 'route' => 'wishes.index', 'active' => 'wishes.*', 'epic' => 'EP-15'],
            ['label' => 'Asistente IA', 'icon' => 'sparkles', 'route' => 'ai.index', 'active' => 'ai.*', 'epic' => 'EP-17'],
            ['label' => 'Mi hogar', 'icon' => 'users', 'route' => 'household.index', 'active' => 'household.*', 'epic' => 'EP-03'],
            ['label' => 'Configuración', 'icon' => 'settings', 'route' => 'settings.index', 'active' => 'settings.*', 'epic' => 'EP-19'],
        ];
    }

    /**
     * ¿La ruta del ítem está implementada y el ítem debe ser navegable?
     *
     * @param  array{route: ?string}  $item
     */
    public static function isAvailable(array $item): bool
    {
        return $item['route'] !== null && Route::has($item['route']);
    }

    /**
     * @param  array{active: string}  $item
     */
    public static function isActive(array $item): bool
    {
        return request()->routeIs($item['active']);
    }
}
