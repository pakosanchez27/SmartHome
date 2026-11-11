<?php

namespace Tests\Feature\Finance;

use App\Actions\RecordTransaction;
use App\Actions\VoidTransaction;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
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
 * EP04-07: corrección y anulación de movimientos (C06).
 *
 * Decisión documentada: la edición solo toca datos descriptivos (concepto,
 * notas, categoría, fecha, visibilidad). Cambiar importe o cuenta exige
 * anular y re-registrar, para que el saldo nunca cambie sin historial (RG-10).
 */
class TransactionCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $ana;

    private User $luis;

    private Account $account;

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

        $this->account = Account::factory()->create([
            'household_id' => $this->household->id,
            'opening_balance' => 100000,
        ]);

        Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Alimentación']);
        Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Transporte']);
    }

    private function expense(User $actor, int $cents = 45050): Transaction
    {
        return app(RecordTransaction::class)->handle($this->household, $actor, [
            'type' => TransactionType::Expense,
            'amount_cents' => $cents,
            'occurred_on' => Carbon::today()->toDateString(),
            'description' => 'Súper de la semana',
            'account_id' => $this->account->id,
            'category_id' => Category::query()->where('name', 'Alimentación')->first()->id,
            'visibility' => 'shared',
        ]);
    }

    public function test_la_edicion_corrigue_datos_descriptivos_sin_tocar_el_dinero(): void
    {
        $transaction = $this->expense($this->ana);

        $response = $this->actingAs($this->ana)->put(route('finance.transactions.update', $transaction), [
            'description' => 'Súper y limpieza',
            'notes' => 'Se pagó con la tarjeta de la despensa',
            'occurred_on' => Carbon::yesterday()->toDateString(),
            'category_id' => Category::query()->where('name', 'Transporte')->first()->id,
            'visibility' => 'shared',
            'amount' => '9999.99', // No está en las reglas: se ignora.
            'account_id' => 999999, // No está en las reglas: se ignora.
        ]);

        $response->assertRedirect(route('finance.transactions.show', $transaction));

        $transaction->refresh();

        $this->assertSame('Súper y limpieza', $transaction->description);
        $this->assertSame('Transporte', $transaction->category->name);
        $this->assertSame(Carbon::yesterday()->toDateString(), $transaction->occurred_on->toDateString());
        $this->assertSame(45050, $transaction->amountCents(), 'El importe no cambia por una edición (RG-02).');
        $this->assertSame(54950, $this->ledger->balance($this->account));

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::UPDATED_TRANSACTION,
            'subject_id' => $transaction->id,
        ]);
    }

    public function test_la_edicion_de_una_transferencia_no_permite_categoria(): void
    {
        $ahorro = Account::factory()->create(['household_id' => $this->household->id]);

        $transfer = app(RecordTransaction::class)->handle($this->household, $this->ana, [
            'type' => TransactionType::Transfer,
            'amount_cents' => 30000,
            'occurred_on' => Carbon::today()->toDateString(),
            'description' => 'Apartado',
            'account_id' => $this->account->id,
            'destination_account_id' => $ahorro->id,
            'category_id' => null,
            'visibility' => 'shared',
        ]);

        $this->actingAs($this->ana)->put(route('finance.transactions.update', $transfer), [
            'description' => 'Apartado semanal',
            'occurred_on' => Carbon::today()->toDateString(),
            'category_id' => Category::query()->where('name', 'Alimentación')->first()->id,
            'visibility' => 'shared',
        ]);

        $transfer->refresh();
        $this->assertNull($transfer->category_id, 'Una transferencia no se categoriza.');
    }

    public function test_la_anulacion_conserva_la_fila_restaura_el_saldo_y_audita(): void
    {
        $transaction = $this->expense($this->ana);
        $this->assertSame(54950, $this->ledger->balance($this->account));

        $response = $this->actingAs($this->luis)->post(route('finance.transactions.annul', $transaction));

        $response->assertRedirect(route('finance.transactions.show', $transaction));

        $transaction->refresh();
        $this->assertSame(TransactionStatus::Canceled, $transaction->status, 'La fila permanece como historia (RG-10).');
        $this->assertSame(1, $transaction->lines()->count(), 'Las líneas tampoco se borran.');
        $this->assertSame(100000, $this->ledger->balance($this->account), 'El saldo vuelve al estado anterior.');

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::CANCELED_TRANSACTION,
            'subject_id' => $transaction->id,
            'actor_user_id' => $this->luis->id,
        ]);
    }

    public function test_un_movimiento_anulado_no_se_vuelve_a_anular_ni_editar(): void
    {
        $transaction = $this->expense($this->ana);
        app(VoidTransaction::class)->handle($transaction, $this->ana);

        $this->actingAs($this->ana)
            ->post(route('finance.transactions.annul', $transaction))
            ->assertForbidden();

        $this->actingAs($this->ana)
            ->put(route('finance.transactions.update', $transaction), [
                'description' => 'Otro concepto',
                'occurred_on' => Carbon::today()->toDateString(),
                'visibility' => 'shared',
            ])
            ->assertForbidden();

        $this->assertSame(TransactionStatus::Canceled, $transaction->refresh()->status);
    }

    public function test_el_rol_consulta_no_puede_anular(): void
    {
        $transaction = $this->expense($this->ana);
        $viewer = User::factory()->create();
        $this->household->users()->attach($viewer, ['role' => 'viewer']);

        $this->actingAs($viewer)
            ->post(route('finance.transactions.annul', $transaction))
            ->assertForbidden();

        $this->assertSame(TransactionStatus::Confirmed, $transaction->refresh()->status);
    }

    public function test_el_detalle_muestra_el_movimiento_y_su_estado(): void
    {
        $transaction = $this->expense($this->ana);

        $this->actingAs($this->luis)
            ->get(route('finance.transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Súper de la semana')
            ->assertSee('Registrado');
    }

    public function test_la_edicion_rechaza_una_categoria_incoherente(): void
    {
        $transaction = $this->expense($this->ana);

        $response = $this->actingAs($this->ana)->put(route('finance.transactions.update', $transaction), [
            'description' => 'Súper',
            'occurred_on' => Carbon::today()->toDateString(),
            'category_id' => 99999,
            'visibility' => 'shared',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_la_edicion_rechaza_una_fecha_futura(): void
    {
        $transaction = $this->expense($this->ana);

        $response = $this->actingAs($this->ana)->put(route('finance.transactions.update', $transaction), [
            'description' => 'Súper',
            'occurred_on' => Carbon::tomorrow()->toDateString(),
            'visibility' => 'shared',
        ]);

        $response->assertSessionHasErrors('occurred_on');
    }
}
