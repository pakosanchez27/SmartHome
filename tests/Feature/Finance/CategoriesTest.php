<?php

namespace Tests\Feature\Finance;

use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * EP04-08: categorías de ingreso y gasto del hogar (C08).
 *
 * Se comparten entre integrantes; desactivar una categoría no rompe el
 * histórico de movimientos que ya la usan (RG-10).
 */
class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $admin;

    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->viewer = User::factory()->create();
        $this->household = Household::factory()->create(['created_by' => $this->admin->id]);
        $this->household->users()->attach($this->admin, ['role' => 'admin']);
        $this->household->users()->attach($this->viewer, ['role' => 'viewer']);
    }

    public function test_la_administra_crea_una_categoria_de_gasto(): void
    {
        $response = $this->actingAs($this->admin)->post(route('finance.categories.store'), [
            'name' => 'Renta',
            'kind' => 'expense',
        ]);

        $response->assertRedirect(route('finance.categories.index'));

        $this->assertDatabaseHas('categories', [
            'household_id' => $this->household->id,
            'name' => 'Renta',
            'kind' => 'expense',
            'is_active' => true,
        ]);
    }

    public function test_el_nombre_no_se_repite_para_el_mismo_tipo(): void
    {
        $this->actingAs($this->admin)->post(route('finance.categories.store'), ['name' => 'Renta', 'kind' => 'expense']);

        $duplicado = $this->actingAs($this->admin)->post(route('finance.categories.store'), ['name' => 'Renta', 'kind' => 'expense']);
        $duplicado->assertSessionHasErrors('name');

        $mismoNombreOtroTipo = $this->actingAs($this->admin)->post(route('finance.categories.store'), ['name' => 'Renta', 'kind' => 'income']);
        $mismoNombreOtroTipo->assertSessionDoesntHaveErrors();
    }

    public function test_una_categoria_de_transferencia_no_existe(): void
    {
        $response = $this->actingAs($this->admin)->post(route('finance.categories.store'), [
            'name' => 'Transferencias',
            'kind' => 'transfer',
        ]);

        $response->assertSessionHasErrors('kind');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_el_rol_consulta_no_crea_categorias(): void
    {
        $response = $this->actingAs($this->viewer)->post(route('finance.categories.store'), [
            'name' => 'Renta',
            'kind' => 'expense',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_se_renombra_y_desactiva_sin_perder_el_historial(): void
    {
        $category = Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Mercado']);

        $account = Account::factory()->create(['household_id' => $this->household->id]);
        $transaction = Transaction::factory()->create([
            'household_id' => $this->household->id,
            'category_id' => $category->id,
        ]);
        $transaction->lines()->create(['account_id' => $account->id, 'amount' => 15000, 'direction' => 'out']);

        $this->actingAs($this->admin)->put(route('finance.categories.update', $category), [
            'name' => 'Alimentación',
            'is_active' => false,
        ]);

        $category->refresh();

        $this->assertSame('Alimentación', $category->name);
        $this->assertFalse($category->is_active, 'Desactivar solo oculta para capturas nuevas.');

        $transaction->refresh();
        $this->assertSame('Alimentación', $transaction->category->name, 'El histórico sigue resolviendo su categoría (RG-10).');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_el_indice_muestra_las_categorias_agrupadas(): void
    {
        Category::factory()->expense()->create(['household_id' => $this->household->id, 'name' => 'Alimentación']);
        Category::factory()->income()->create(['household_id' => $this->household->id, 'name' => 'Nómina']);

        $this->actingAs($this->viewer)
            ->get(route('finance.categories.index'))
            ->assertOk()
            ->assertSee('Alimentación')
            ->assertSee('Nómina');
    }
}
