<?php

namespace Tests\Feature\Finance;

use App\Actions\RecordTransaction;
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
 * EP04-10: privacidad financiera entre integrantes (RG-01, EP03-07).
 *
 * Una cuenta privada no aparece en listados, detalle directo, filtros ni
 * agregados del otro integrante, aunque sea administrador. Un movimiento
 * sobre una cuenta privada queda privado por decisión del servidor.
 */
class FinancePrivacyTest extends TestCase
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

        Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Alimentación']);
    }

    private function privateAccountOfAna(): Account
    {
        return Account::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Efectivo de Ana',
            'owner_user_id' => $this->ana->id,
            'shared_at' => null,
            'opening_balance' => 800000,
        ]);
    }

    private function expenseOn(Account $account, User $actor, string $description): Transaction
    {
        return app(RecordTransaction::class)->handle($this->household, $actor, [
            'type' => TransactionType::Expense,
            'amount_cents' => 45000,
            'occurred_on' => Carbon::today()->toDateString(),
            'description' => $description,
            'account_id' => $account->id,
            'category_id' => Category::query()->where('name', 'Alimentación')->first()->id,
            'visibility' => 'shared',
        ]);
    }

    public function test_la_cuenta_privada_no_aparece_en_el_panorama_del_otro(): void
    {
        $this->privateAccountOfAna();

        $responseLuis = $this->actingAs($this->luis)->get(route('finance.index'));
        $responseLuis->assertOk();
        $responseLuis->assertDontSee('Efectivo de Ana');

        $responseAna = $this->actingAs($this->ana)->get(route('finance.index'));
        $responseAna->assertOk()->assertSee('Efectivo de Ana');
    }

    public function test_el_panorama_del_otro_no_suma_el_saldo_privado(): void
    {
        $this->privateAccountOfAna(); // $8,000 privados de Ana.

        $this->assertSame(800000, $this->ledger->cashAvailable($this->household, $this->ana));
        $this->assertSame(0, $this->ledger->cashAvailable($this->household, $this->luis), 'El disponible de Luis no incluye lo privado de Ana.');
    }

    public function test_la_cuenta_privada_no_se_abre_por_url_directa(): void
    {
        $account = $this->privateAccountOfAna();

        $this->actingAs($this->luis)
            ->get(route('finance.accounts.show', $account))
            ->assertForbidden();

        $this->actingAs($this->ana)
            ->get(route('finance.accounts.show', $account))
            ->assertOk();
    }

    public function test_un_movimiento_sobre_cuenta_privada_queda_privado_ante_una_peticion_compartida(): void
    {
        $account = $this->privateAccountOfAna();

        // Ana intenta compartir el movimiento; el servidor lo impone privado
        // porque la cuenta implicada es personal (ADR-0002).
        $transaction = app(RecordTransaction::class)->handle($this->household, $this->ana, [
            'type' => TransactionType::Expense,
            'amount_cents' => 45000,
            'occurred_on' => Carbon::today()->toDateString(),
            'description' => 'Regalo sorpresa',
            'account_id' => $account->id,
            'category_id' => Category::query()->where('name', 'Alimentación')->first()->id,
            'visibility' => 'shared',
        ]);

        $this->assertSame($this->ana->id, $transaction->owner_user_id, 'El movimiento hereda la privacidad de su cuenta.');
        $this->assertFalse($transaction->isShared());

        $this->actingAs($this->luis)
            ->get(route('finance.transactions.show', $transaction))
            ->assertForbidden();
    }

    public function test_el_listado_del_otro_no_muestra_movimientos_privados(): void
    {
        $account = $this->privateAccountOfAna();
        $this->expenseOn($account, $this->ana, 'Compra personal de Ana');

        $compartida = Account::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Débito familiar',
            'opening_balance' => 0,
        ]);
        $this->expenseOn($compartida, $this->ana, 'Súper de la casa');

        $response = $this->actingAs($this->luis)->get(route('finance.transactions.index'));
        $response->assertOk();
        $response->assertSee('Súper de la casa');
        $response->assertDontSee('Compra personal de Ana');
    }

    public function test_los_agregados_del_otro_excluyen_los_movimientos_privados(): void
    {
        $privada = $this->privateAccountOfAna();
        $this->expenseOn($privada, $this->ana, 'Compra personal de Ana');

        $compartida = Account::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Débito familiar',
            'opening_balance' => 0,
        ]);
        $this->expenseOn($compartida, $this->ana, 'Súper de la casa');

        $from = Carbon::today()->startOfMonth();
        $to = Carbon::today();

        $totalesAna = $this->ledger->periodTotals($this->household, $this->ana, $from, $to);
        $totalesLuis = $this->ledger->periodTotals($this->household, $this->luis, $from, $to);

        $this->assertSame(90000, $totalesAna['expense'], 'Ana ve su gasto privado y el compartido.');
        $this->assertSame(45000, $totalesLuis['expense'], 'Luis solo ve el gasto compartido.');
    }

    public function test_un_integrante_no_ve_cuentas_de_otro_hogar(): void
    {
        $otroHogar = Household::factory()->create();
        $cuentaAjena = Account::factory()->create(['household_id' => $otroHogar->id, 'name' => 'Cuenta de otro hogar']);

        $this->actingAs($this->ana)
            ->get(route('finance.accounts.show', $cuentaAjena))
            ->assertForbidden();
    }

    public function test_un_integrante_desactivado_no_puede_entrar_al_modulo(): void
    {
        $suspendido = User::factory()->create();
        $this->household->users()->attach($suspendido, ['role' => 'member', 'deactivated_at' => now()]);

        $this->actingAs($suspendido)
            ->get(route('finance.index'))
            ->assertForbidden();
    }

    public function test_una_invitada_sin_hogar_no_puede_entrar_al_modulo(): void
    {
        $sinHogar = User::factory()->create();

        $this->actingAs($sinHogar)
            ->get(route('finance.index'))
            ->assertForbidden();
    }

    public function test_las_categorias_compartidas_las_ve_todos(): void
    {
        // Las categorías son del hogar: no hay categorías privadas (EP04-08).
        $response = $this->actingAs($this->luis)->get(route('finance.categories.index'));

        $response->assertOk()->assertSee('Alimentación');
    }
}
