<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_al_usuario_crea_el_hogar_y_lo_deja_como_administrador(): void
    {
        $response = $this->post(route('household.register.store'), [
            'name' => 'Ana Rosales',
            'email' => 'ana@example.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
            'household_name' => 'Casa Rosales',
            'timezone' => 'America/Mexico_City',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $household = Household::where('name', 'Casa Rosales')->first();
        $user = Household::first()->users()->where('email', 'ana@example.com')->first();

        $this->assertNotNull($household);
        $this->assertSame('MXN', $household->fresh()->currency);
        $this->assertSame('America/Mexico_City', $household->fresh()->timezone);
        $this->assertTrue($household->fresh()->budget_carryover);
        $this->assertSame(15, $household->fresh()->cutoff_day);
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->pivot->role);
        $this->assertSame($user->id, $household->fresh()->created_by);
    }

    public function test_guarda_la_sesion_del_hogar_actual(): void
    {
        $householdId = null;

        $this->post(route('household.register.store'), [
            'name' => 'Ana Rosales',
            'email' => 'ana@example.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
            'household_name' => 'Casa Rosales',
            'timezone' => 'America/Merida',
        ])->assertSessionHas('current_household_id', function ($id) use (&$householdId) {
            $householdId = $id;

            return is_numeric($id);
        });

        $this->assertNotNull($householdId);
        $this->assertDatabaseHas('households', ['id' => $householdId, 'timezone' => 'America/Merida']);
    }

    public function test_correo_duplicado_no_crea_ni_usuario_ni_hogar(): void
    {
        // EP03-02: un usuario puede pertenecer a un hogar ya creado; aquí el correo repite.
        $existing = User::factory()->create(['name' => 'Ana Titular', 'email' => 'ana@example.com']);
        $creator = User::factory()->create();

        Household::factory()->create([
            'name' => 'Casa Rosales',
            'created_by' => $creator->id,
        ]);

        $this->post(route('household.register.store'), [
            'name' => 'Ana Otra',
            'email' => 'ana@example.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
            'household_name' => 'Casa distinta',
            'timezone' => 'America/Mexico_City',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com', 'name' => 'Ana Otra']);
        $this->assertSame(1, Household::count());
        $this->assertNotNull($existing->name);
    }

    public function test_valida_zoneas_horarias_desconocidas(): void
    {
        $response = $this->post(route('household.register.store'), [
            'name' => 'Ana Rosales',
            'email' => 'ana@example.com',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
            'household_name' => 'Casa Rosales',
            'timezone' => 'Venta/Galaxy',
        ]);

        $response->assertSessionHasErrors('timezone');
        $this->assertGuest();
        $this->assertDatabaseCount('households', 0);
    }

    public function test_requiere_datos_de_cuenta_y_hogar(): void
    {
        $response = $this->post(route('household.register.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'household_name', 'timezone']);
        $this->assertGuest();
    }
}
