<?php

namespace Tests\Feature;

use App\Support\Navigation;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_la_biblioteca_ui_se_renderiza(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Sistema de diseño');
        $response->assertSee('Color funcional');
        $response->assertSee('Botones · 44 px mínimo');
        $response->assertSee('Feedback y estados');
    }

    public function test_el_shell_incluye_navegacion_desktop_y_movil(): void
    {
        $response = $this->get('/');

        $response->assertSee('aria-label="Navegación principal"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Inicio');
        $response->assertSee('Finanzas');
        $response->assertSee('Compras');
        $response->assertSee('Alimentación');
        $response->assertSee('Más');
    }

    public function test_los_modulos_sin_ruta_se_muestran_deshabilitados_y_no_como_enlaces_muertos(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $pendientes = collect(Navigation::main())
            ->filter(fn (array $item) => ! Navigation::isAvailable($item));

        $this->assertNotEmpty($pendientes, 'Debe existir al menos un módulo pendiente para probar el estado deshabilitado.');

        $response->assertSee('aria-disabled="true"', false);
        $response->assertDontSee('href="#"', false);

        foreach ($pendientes as $item) {
            $this->assertStringContainsString(
                'disponible en '.$item['epic'],
                $html,
                "El módulo «{$item['label']}» deshabilitado debe anunciar su épica."
            );
        }
    }

    public function test_el_shell_cumple_accesibilidad_basica(): void
    {
        $response = $this->get('/');

        $response->assertSee('Saltar al contenido');
        $response->assertSee('lang="es-MX"', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('aria-live="polite"', false);
    }

    public function test_los_formularios_asocian_label_e_input(): void
    {
        $response = $this->get('/');

        $response->assertSee('for="field-demo-concepto"', false);
        $response->assertSee('id="field-demo-concepto"', false);
        $response->assertSee('aria-invalid="true"', false);
        $response->assertSee('Ingresa un importe mayor que cero.');
    }
}
