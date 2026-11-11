<?php

namespace Tests\Feature\Finance;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * EP04-04: registro de ingresos, gastos y transferencias (C05/C13).
 *
 * El servidor valida tipo, importe positivo con a lo más dos decimales,
 * cuentas del propio hogar, categoría coherente y fecha no futura. Un ID
 * ajeno o privado no pasa aunque venga en el formulario (RG-01).
 */
class RecordTransactionTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $ana;

    private Account $account;

    private LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledger = app(LedgerService::class);

        $this->ana = User::factory()->create();
        $luis = User::factory()->create();
        $this->household = Household::factory()->create(['created_by' => $this->ana->id]);
        $this->household->users()->attach($this->ana, ['role' => 'admin']);
        $this->household->users()->attach($luis, ['role' => 'member']);

        $this->account = Account::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Débito familiar',
            'opening_balance' => 100000,
        ]);

        Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Alimentación']);
        Category::factory()->income()->create(['household_id' => $this->household->id, 'name' => 'Nómina']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'expense',
            'amount' => '450.50',
            'occurred_on' => Carbon::today()->toDateString(),
            'description' => 'Súper de la semana',
            'notes' => null,
            'account_id' => $this->account->id,
            'category_id' => Category::query()->where('name', 'Alimentación')->first()->id,
            'visibility' => 'shared',
        ], $overrides);
    }

    public function test_registra_un_gasto_y_descuenta_el_saldo_una_vez(): void
    {
        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload());

        $transaction = Transaction::query()->first();
        $response->assertRedirect(route('finance.transactions.show', $transaction));

        $this->assertSame(1, $transaction->lines()->count());
        $this->assertSame(45050, $transaction->amountCents());
        $this->assertSame(54950, $this->ledger->balance($this->account), '1,000 − 450.50 = 549.50 pesos.');

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::RECORDED_TRANSACTION,
            'subject_id' => $transaction->id,
            'actor_user_id' => $this->ana->id,
        ]);
    }

    public function test_registra_un_ingreso_en_una_categoria_de_ingreso(): void
    {
        $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'type' => 'income',
            'amount' => '1200',
            'description' => 'Nómina quincenal',
            'category_id' => Category::query()->where('name', 'Nómina')->first()->id,
        ]));

        $transaction = Transaction::query()->first();

        $this->assertSame('income', $transaction->type->value);
        $this->assertSame(220000, $this->ledger->balance($this->account));
    }

    public function test_la_transferencia_genera_dos_lineas_y_redirige_al_detalle(): void
    {
        $ahorro = Account::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Ahorro',
            'opening_balance' => 0,
        ]);

        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'type' => 'transfer',
            'amount' => '300',
            'destination_account_id' => $ahorro->id,
            'category_id' => null,
            'description' => 'Apartado semanal',
        ]));

        $transaction = Transaction::query()->first();
        $response->assertRedirect(route('finance.transactions.show', $transaction));

        $this->assertSame(2, $transaction->lines()->count(), 'Salida y entrada por el mismo importe (RG-03).');
        $this->assertSame(30000, $transaction->amountCents(), 'El importe de la transferencia no se cuenta doble.');
        $this->assertSame(70000, $this->ledger->balance($this->account));
        $this->assertSame(30000, $this->ledger->balance($ahorro));
    }

    public function test_rechaza_importes_invalidos(): void
    {
        foreach (['0', '-50', '12.345', 'abc', ''] as $invalido) {
            $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload(['amount' => $invalido]));

            $response->assertSessionHasErrors('amount', "El importe «{$invalido}» debe rechazarse en servidor.");
            $this->assertSame(0, Transaction::count(), 'Nada se guarda a medias.');
        }
    }

    public function test_rechaza_una_fecha_futura(): void
    {
        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'occurred_on' => Carbon::tomorrow()->toDateString(),
        ]));

        $response->assertSessionHasErrors('occurred_on');
    }

    public function test_rechaza_transferencia_a_la_misma_cuenta(): void
    {
        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'type' => 'transfer',
            'destination_account_id' => $this->account->id,
            'category_id' => null,
        ]));

        $response->assertSessionHasErrors('destination_account_id');
    }

    public function test_rechaza_transferencia_sin_destino(): void
    {
        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'type' => 'transfer',
            'destination_account_id' => null,
            'category_id' => null,
        ]));

        $response->assertSessionHasErrors('destination_account_id');
    }

    public function test_rechaza_categoria_de_otro_tipo(): void
    {
        $nomina = Category::query()->where('name', 'Nómina')->first();

        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'category_id' => $nomina->id,
        ]));

        $response->assertSessionHasErrors('category_id', 'Un gasto con categoría de ingreso no pasa.');
    }

    public function test_rechaza_cuentas_de_otro_hogar(): void
    {
        $otroHogar = Household::factory()->create();
        $cuentaAjena = Account::factory()->create(['household_id' => $otroHogar->id]);

        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'account_id' => $cuentaAjena->id,
        ]));

        $response->assertSessionHasErrors('account_id', 'Un ID ajeno en el formulario no revela ni acepta la cuenta (RG-01).');
    }

    public function test_rechaza_cuenta_inactiva(): void
    {
        $inactiva = Account::factory()->create(['household_id' => $this->household->id, 'is_active' => false]);

        $response = $this->actingAs($this->ana)->post(route('finance.transactions.store'), $this->payload([
            'account_id' => $inactiva->id,
        ]));

        $response->assertSessionHasErrors('account_id');
    }

    public function test_el_rol_consulta_no_registra_movimientos(): void
    {
        $viewer = User::factory()->create();
        $this->household->users()->attach($viewer, ['role' => 'viewer']);

        $response = $this->actingAs($viewer)->post(route('finance.transactions.store'), $this->payload());

        $response->assertForbidden();
        $this->assertSame(0, Transaction::count());
    }

    public function test_la_vista_de_registro_carga_cuentas_y_categorias_visibles(): void
    {
        $this->actingAs($this->ana)
            ->get(route('finance.transactions.create'))
            ->assertOk()
            ->assertSee('Débito familiar')
            ->assertSee('Alimentación');
    }

    public function test_el_listado_filtra_por_tipo_y_busqueda(): void
    {
        $gasto = Transaction::factory()->create(['household_id' => $this->household->id, 'description' => 'Súper']);
        $gasto->lines()->create(['account_id' => $this->account->id, 'amount' => 10000, 'direction' => 'out']);

        $ingreso = Transaction::factory()->income()->create(['household_id' => $this->household->id, 'description' => 'Nómina']);
        $ingreso->lines()->create(['account_id' => $this->account->id, 'amount' => 20000, 'direction' => 'in']);

        $this->actingAs($this->ana)
            ->get(route('finance.transactions.index', ['type' => 'income']))
            ->assertOk()
            ->assertSee('Nómina')
            ->assertDontSee('Súper');

        $this->actingAs($this->ana)
            ->get(route('finance.transactions.index', ['q' => 'Nómina']))
            ->assertOk()
            ->assertSee('Nómina')
            ->assertDontSee('Súper');
    }

    public function test_el_listado_por_cuenta_privada_ajena_responde_404(): void
    {
        $privada = Account::factory()->create([
            'household_id' => $this->household->id,
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $luis = User::factory()->create();
        $this->household->users()->attach($luis, ['role' => 'member']);

        $this->actingAs($luis)
            ->get(route('finance.transactions.index', ['account_id' => $privada->id]))
            ->assertNotFound();
    }

    public function test_un_filtro_invalido_en_el_listado_redirige_con_errores(): void
    {
        $this->actingAs($this->ana)
            ->get(route('finance.transactions.index', ['type' => 'prestamo']))
            ->assertRedirect()
            ->assertSessionHasErrors('type');
    }
}
