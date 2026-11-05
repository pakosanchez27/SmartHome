<?php

namespace Tests\Feature;

use App\Support\Navigation;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    public function test_la_navegacion_principal_respeta_el_orden_de_figma(): void
    {
        $labels = array_column(Navigation::main(), 'label');

        $this->assertSame([
            'Inicio',
            'Finanzas',
            'Alimentación',
            'Despensa',
            'Compras',
            'Planes',
            'Necesidades y metas',
            'Asistente IA',
            'Mi hogar',
            'Configuración',
        ], $labels);
    }

    public function test_la_barra_movil_solo_tiene_los_cuatro_destinos_mas_el_boton_mas(): void
    {
        $labels = array_column(Navigation::mobile(), 'label');

        $this->assertSame(['Inicio', 'Finanzas', 'Compras', 'Alimentación'], $labels);
    }

    public function test_la_hoja_mas_contiene_los_modulos_secundarios(): void
    {
        $labels = array_column(Navigation::more(), 'label');

        $this->assertSame([
            'Despensa',
            'Planes',
            'Necesidades y metas',
            'Asistente IA',
            'Mi hogar',
            'Configuración',
        ], $labels);
    }

    public function test_un_item_sin_ruta_implementada_no_es_navegable(): void
    {
        $items = collect(Navigation::main())->keyBy('label');

        $this->assertTrue(Navigation::isAvailable($items['Inicio']));
        $this->assertFalse(Navigation::isAvailable($items['Finanzas']));
        $this->assertFalse(Navigation::isAvailable($items['Despensa']));
    }

    public function test_los_items_no_se_marcan_activos_fuera_de_su_ruta(): void
    {
        $this->get('/');

        $items = collect(Navigation::main())->keyBy('label');

        $this->assertTrue(Navigation::isActive($items['Inicio']));
        $this->assertFalse(Navigation::isActive($items['Finanzas']));
    }
}
