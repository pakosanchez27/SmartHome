<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrototypeTest extends TestCase
{
    public function test_el_indice_distingue_los_flujos_disponibles(): void
    {
        $this->get(route('prototypes.index'))
            ->assertOk()
            ->assertSee('Datos ficticios y sin persistencia')
            ->assertSee('Registrar un gasto')
            ->assertSee('Presupuesto quincenal')
            ->assertSee('Lista de compras')
            ->assertSee('Despensa y stock')
            ->assertDontSee('Prototipo en construcción');
    }

    public function test_el_gasto_es_una_demostracion_sin_persistencia(): void
    {
        $this->get(route('prototypes.expense'))
            ->assertOk()
            ->assertSee('No se crea ningún egreso')
            ->assertSee('expenseFlow()', false);
    }

    public function test_el_presupuesto_filtra_y_no_confunde_el_plan_con_saldo(): void
    {
        $this->get(route('prototypes.budget', ['categoria' => 'food']))
            ->assertOk()
            ->assertSee('Supermercado semanal')
            ->assertDontSee('Renta de noviembre')
            ->assertSee('No equivale al efectivo disponible.');
    }

    public function test_el_menu_es_una_demostracion_sin_consumo_de_despensa(): void
    {
        $this->get(route('prototypes.meals'))
            ->assertOk()
            ->assertSee('Planificador semanal')
            ->assertSee('Planificar no descuenta existencias')
            ->assertSee('mealPlanner()', false)
            ->assertSee('Seleccionar día');
    }

    public function test_los_prototipos_de_compra_y_despensa_son_demostraciones(): void
    {
        $this->get(route('prototypes.shopping'))
            ->assertOk()
            ->assertSee('Supermercado')
            ->assertSee('confirmar tampoco registra egresos ni modifica existencias');

        $this->get(route('prototypes.pantry'))
            ->assertOk()
            ->assertSee('Inventario de despensa')
            ->assertSee('No actualizan el inventario del hogar');
    }
}
