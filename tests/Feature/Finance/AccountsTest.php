<?php

namespace Tests\Feature\Finance;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * EP04-02: alta y edición de cuentas (C04) con validación server-side.
 *
 * El saldo inicial se captura una vez; después el saldo se reconstruye con
 * movimientos, nunca con una edición directa (RG-02).
 */
class AccountsTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $admin;

    private User $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->partner = User::factory()->create();
        $this->household = Household::factory()->create(['created_by' => $this->admin->id]);
        $this->household->users()->attach($this->admin, ['role' => 'admin']);
        $this->household->users()->attach($this->partner, ['role' => 'member']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Efectivo',
            'type' => 'cash',
            'opening_balance' => '2500.00',
            'visibility' => 'shared',
        ], $overrides);
    }

    public function test_la_administra_crea_una_cuenta_con_saldo_inicial(): void
    {
        $response = $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload());

        $response->assertRedirect();

        $account = Account::query()->where('household_id', $this->household->id)->first();
        $this->assertNotNull($account);
        $this->assertSame('Efectivo', $account->name);
        $this->assertSame(250000, $account->opening_balance, 'El saldo inicial viaja como centavos exactos.');
        $this->assertNull($account->owner_user_id, 'Compartida = cuenta del hogar, sin titular.');
        $this->assertTrue($account->is_active);
        $this->assertSame('MXN', $account->currency);

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::CREATED_ACCOUNT,
            'subject_id' => $account->id,
        ]);
        $this->assertTrue(
            ActivityLog::query()->where('action', ActivityLog::CREATED_ACCOUNT)->where('subject_id', $account->id)->exists(),
            'Toda operación sensible se audita (RG-10).'
        );
    }

    public function test_la_tarjeta_de_credito_guarda_su_limite_y_otro_tipo_lo_ignora(): void
    {
        $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload([
            'name' => 'Tarjeta',
            'type' => 'credit',
            'credit_limit' => '8000.00',
            'opening_balance' => '0',
        ]));

        $credit = Account::query()->where('name', 'Tarjeta')->first();
        $this->assertSame(800000, $credit->credit_limit, 'El límite se guarda para calcular el disponible.');

        $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload([
            'name' => 'Débito',
            'type' => 'debit',
            'credit_limit' => '8000.00',
            'opening_balance' => '0',
        ]));

        $debit = Account::query()->where('name', 'Débito')->first();
        $this->assertNull($debit->credit_limit, 'Un límite de crédito en cuenta no-crédito se descarta.');
    }

    public function test_el_nombre_se_repite_dentro_del_mismo_alcance(): void
    {
        $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload());

        $response = $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload());

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, Account::query()->where('household_id', $this->household->id)->count());
    }

    public function test_dos_personas_pueden_tener_su_propia_cuenta_con_el_mismo_nombre(): void
    {
        $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload(['visibility' => 'private']));
        $response = $this->actingAs($this->partner)->post(route('finance.accounts.store'), $this->validPayload(['visibility' => 'private']));

        $response->assertSessionDoesntHaveErrors();

        $this->assertSame(2, Account::query()->where('name', 'Efectivo')->count(), 'Dos cuentas privadas con el mismo nombre, una por persona.');
    }

    public function test_el_saldo_inicial_invalido_se_rechaza(): void
    {
        // Un saldo inicial negativo es válido (deuda existente en tarjeta);
        // lo que se rechaza es lo que no puede interpretarse como dinero.
        foreach (['', 'abc', '12.345'] as $invalido) {
            $response = $this->actingAs($this->admin)->post(route('finance.accounts.store'), $this->validPayload(['opening_balance' => $invalido]));

            $response->assertSessionHasErrors('opening_balance', "«{$invalido}» debe rechazarse como saldo inicial.");
        }
    }

    public function test_la_edicion_no_toca_el_saldo_inicial(): void
    {
        $account = Account::factory()->create([
            'household_id' => $this->household->id,
            'opening_balance' => 250000,
        ]);

        $response = $this->actingAs($this->admin)->put(route('finance.accounts.update', $account), [
            'name' => 'Efectivo de la casa',
            'type' => 'cash',
            'visibility' => 'shared',
            'is_active' => true,
            'opening_balance' => '999999.99', // Se ignora: no está en las reglas de edición.
        ]);

        $response->assertRedirect();

        $account->refresh();
        $this->assertSame('Efectivo de la casa', $account->name);
        $this->assertSame(250000, $account->opening_balance, 'Cambiar el saldo reescribiría la historia (RG-02).');
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityLog::UPDATED_ACCOUNT, 'subject_id' => $account->id]);
    }

    public function test_la_edicion_puede_desactivar_la_cuenta_sin_borrarla(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        $this->actingAs($this->admin)->put(route('finance.accounts.update', $account), [
            'name' => $account->name,
            'type' => $account->type->value,
            'visibility' => 'shared',
            'is_active' => false,
        ]);

        $account->refresh();
        $this->assertFalse($account->is_active);
        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_el_rol_consulta_no_puede_crear_cuentas(): void
    {
        $viewer = User::factory()->create();
        $this->household->users()->attach($viewer, ['role' => 'viewer']);

        $response = $this->actingAs($viewer)->post(route('finance.accounts.store'), $this->validPayload());

        $response->assertForbidden();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_no_existe_ruta_para_borrar_cuentas(): void
    {
        $this->assertFalse(Route::has('finance.accounts.destroy'), 'Las cuentas se desactivan, no se borran (RG-10).');
    }

    public function test_la_administra_ve_el_detalle_de_su_cuenta(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id, 'name' => 'Efectivo']);

        $this->actingAs($this->admin)
            ->get(route('finance.accounts.show', $account))
            ->assertOk()
            ->assertSee('Efectivo');
    }

    public function test_un_filtro_invalido_redirige_con_errores(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        $this->actingAs($this->admin)
            ->get(route('finance.accounts.show', ['account' => $account, 'type' => 'prestamo']))
            ->assertRedirect()
            ->assertSessionHasErrors('type');
    }

    public function test_un_movimiento_registrado_aparece_en_el_detalle_de_la_cuenta(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);
        $transaction = Transaction::factory()->create([
            'household_id' => $this->household->id,
            'description' => 'Súper de la semana',
        ]);
        $transaction->lines()->create(['account_id' => $account->id, 'amount' => 45000, 'direction' => 'out']);

        $this->actingAs($this->admin)
            ->get(route('finance.accounts.show', $account))
            ->assertOk()
            ->assertSee('Súper de la semana');
    }
}
