<?php

namespace Tests\Feature\Finance;

use App\Actions\RecordTransaction;
use App\Actions\VoidTransaction;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * EP04-10: reconstrucción de saldos y estados monetarios (RG-02, RG-03, RG-08).
 *
 * El saldo no es una columna editable: sale del saldo inicial más las líneas
 * de movimientos confirmados. Pendientes y anulados no cuentan, y una
 * transferencia nunca altera los ingresos/egresos agregados del hogar.
 */
class LedgerBalanceTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $ana;

    private User $luis;

    private LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledger = app(LedgerService::class);

        $this->ana = User::factory()->create();
        $this->luis = User::factory()->create();
        $this->household = Household::factory()->create(['created_by' => $this->ana->id]);
        $this->household->users()->attach($this->ana, ['role' => 'admin']);
        $this->household->users()->attach($this->luis, ['role' => 'member']);
    }

    /** Cuenta del hogar (sin titular) con saldo inicial en centavos. */
    private function account(int $openingCents, array $attributes = []): Account
    {
        return Account::factory()->create(array_merge([
            'household_id' => $this->household->id,
            'name' => 'Cuenta '.uniqid(),
            'opening_balance' => $openingCents,
        ], $attributes));
    }

    private function record(User $actor, TransactionType $type, int $cents, Account $from, ?Account $to = null, array $extra = []): Transaction
    {
        $category = null;

        if (! $type->isTransfer() && ! isset($extra['category_id'])) {
            $category = Category::factory()->create([
                'household_id' => $this->household->id,
                'kind' => $type,
            ]);
        }

        return app(RecordTransaction::class)->handle($this->household, $actor, [
            'type' => $type,
            'amount_cents' => $cents,
            'occurred_on' => $extra['occurred_on'] ?? Carbon::today()->toDateString(),
            'description' => $extra['description'] ?? 'Movimiento de prueba',
            'account_id' => $from->id,
            'destination_account_id' => $to?->id,
            'category_id' => $extra['category_id'] ?? $category?->id,
            'visibility' => $extra['visibility'] ?? 'shared',
        ]);
    }

    public function test_el_saldo_se_reconstruye_desde_el_saldo_inicial_y_los_confirmados(): void
    {
        $account = $this->account(500000);
        $this->assertSame(500000, $this->ledger->balance($account));

        $this->record($this->ana, TransactionType::Income, 250000, $account, null, ['description' => 'Nómina']);
        $this->record($this->ana, TransactionType::Expense, 150000, $account, null, ['description' => 'Súper']);

        $this->assertSame(600000, $this->ledger->balance($account), '5,000 + 2,500 − 1,500 = 6,000 pesos exactos.');
    }

    public function test_los_movimientos_pendientes_no_alteran_el_saldo(): void
    {
        $account = $this->account(100000);

        Transaction::factory()
            ->pending()
            ->create(['household_id' => $this->household->id])
            ->lines()
            ->create(['account_id' => $account->id, 'amount' => 60000, 'direction' => 'out']);

        $this->assertSame(100000, $this->ledger->balance($account), 'Lo previsto (pending) no es dinero movido (RG-08).');
    }

    public function test_los_movimientos_anulados_dejan_de_contar_y_el_historial_se_conserva(): void
    {
        $account = $this->account(100000);
        $expense = $this->record($this->ana, TransactionType::Expense, 40000, $account);

        $this->assertSame(60000, $this->ledger->balance($account));

        app(VoidTransaction::class)->handle($expense, $this->ana);

        $expense->refresh();
        $this->assertSame(TransactionStatus::Canceled, $expense->status, 'La fila no se borra (RG-10).');
        $this->assertSame(100000, $this->ledger->balance($account), 'Anulado deja de contar exactamente una vez.');
    }

    public function test_una_transferencia_mueve_dinero_sin_crear_ingreso_ni_egreso(): void
    {
        $efectivo = $this->account(100000, ['name' => 'Efectivo']);
        $ahorro = $this->account(0, ['name' => 'Ahorro']);

        $transfer = $this->record($this->ana, TransactionType::Transfer, 50000, $efectivo, $ahorro, ['description' => 'Apartado']);

        $this->assertSame(50000, $this->ledger->balance($efectivo));
        $this->assertSame(50000, $this->ledger->balance($ahorro));

        $lines = $transfer->lines()->get();
        $this->assertCount(2, $lines, 'Una transferencia son dos líneas: salida y entrada.');

        $from = Carbon::today()->startOfMonth();
        $to = Carbon::today();
        $totals = $this->ledger->periodTotals($this->household, $this->ana, $from, $to);

        $this->assertSame(0, $totals['income'], 'La transferencia no es un ingreso del hogar (RG-03).');
        $this->assertSame(0, $totals['expense'], 'La transferencia no es un egreso del hogar (RG-03).');
        $this->assertSame(0, $totals['net']);
    }

    public function test_los_totales_del_periodo_suman_ingresos_y_egresos_confirmados(): void
    {
        $account = $this->account(0);

        $this->record($this->ana, TransactionType::Income, 300000, $account, null, ['description' => 'Nómina quincena']);
        $this->record($this->luis, TransactionType::Expense, 125000, $account, null, ['description' => 'Gasolina']);
        $this->record($this->ana, TransactionType::Expense, 75000, $account, null, ['description' => 'Farmacia']);

        $totals = $this->ledger->periodTotals($this->household, $this->ana, Carbon::today()->startOfMonth(), Carbon::today());

        $this->assertSame(300000, $totals['income']);
        $this->assertSame(200000, $totals['expense']);
        $this->assertSame(100000, $totals['net'], 'Dos integrantes registran y cada peso cuenta una sola vez.');
    }

    public function test_el_limite_de_credito_no_se_suma_al_efectivo_disponible(): void
    {
        $credit = $this->account(0, ['name' => 'Crédito', 'type' => 'credit', 'credit_limit' => 500000]);
        $cash = $this->account(100000, ['name' => 'Efectivo']);

        $this->assertSame(500000, $this->ledger->available($credit), 'Sin deuda, el disponible de la tarjeta es su límite.');
        $this->assertSame(0, $this->ledger->owed($credit));

        $this->record($this->ana, TransactionType::Expense, 200000, $credit, null, ['description' => 'Compra con tarjeta']);

        $this->assertSame(-200000, $this->ledger->balance($credit));
        $this->assertSame(200000, $this->ledger->owed($credit), 'La deuda se calcula, no se inventa (RG-11).');
        $this->assertSame(300000, $this->ledger->available($credit));

        $this->assertSame(
            100000,
            $this->ledger->cashAvailable($this->household, $this->ana),
            'El efectivo disponible del hogar solo suma cuentas reales; el crédito es pasivo (RG-02).'
        );
    }

    public function test_el_saldo_a_favor_en_credito_no_infla_el_disponible(): void
    {
        $credit = $this->account(50000, ['name' => 'Crédito', 'type' => 'credit', 'credit_limit' => 500000]);

        $this->assertSame(500000, $this->ledger->available($credit), 'Un saldo a favor no amplía el límite.');
    }

    public function test_cuentas_inactivas_quedan_fuera_del_efectivo_disponible(): void
    {
        $this->account(100000, ['name' => 'Cuenta cerrada', 'is_active' => false]);
        $this->account(250000, ['name' => 'Cuenta activa']);

        $this->assertSame(250000, $this->ledger->cashAvailable($this->household, $this->ana));
    }
}
