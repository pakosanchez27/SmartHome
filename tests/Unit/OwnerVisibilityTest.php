<?php

namespace Tests\Unit;

use App\Enums\Visibility;
use App\Models\Concerns\HasOwnerVisibility;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * EP03-07: contrato de visibilidad (ADR-0002) probado sobre una tabla
 * temporal SQLite; las tablas reales llegarán con EP-04/EP-15 siguiendo
 * este mismo contrato (household_id + owner_user_id + shared_at).
 */
class OwnerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private object $modelClass;

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection()->statement(
            'CREATE TEMP TABLE pilot_resources (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                household_id INTEGER NOT NULL,
                owner_user_id INTEGER NULL,
                shared_at DATETIME NULL,
                label TEXT NOT NULL
            )'
        );

        $this->modelClass = new class extends Model
        {
            /** @use HasOwnerVisibility<static> */
            use HasOwnerVisibility;

            protected $table = 'pilot_resources';

            public $timestamps = false;

            protected $guarded = [];
        };
    }

    protected function tearDown(): void
    {
        DB::connection()->statement('DROP TABLE IF EXISTS pilot_resources');
        parent::tearDown();
    }

    /** Inserta una fila piloto y devuelve su id. */
    private function pilotRow(?int $ownerId, ?string $sharedAt, string $label, int $householdId): int
    {
        return DB::table('pilot_resources')->insertGetId([
            'household_id' => $householdId,
            'owner_user_id' => $ownerId,
            'shared_at' => $sharedAt,
            'label' => $label,
        ]);
    }

    /** @return array{0: Household, 1: User, 2: User} */
    private function householdWithTwoActiveMembers(): array
    {
        $ana = User::factory()->create();
        $luis = User::factory()->create();
        $household = Household::factory()->create(['created_by' => $ana->id]);
        $household->users()->attach($ana, ['role' => 'admin']);
        $household->users()->attach($luis, ['role' => 'member']);

        return [$household, $ana, $luis];
    }

    private function recurso(int $id): Model
    {
        $class = $this->modelClass::class;

        /** @var Model&HasOwnerVisibility $resource */
        $resource = $class::query()->findOrFail($id);
        $resource->mergeCasts(['shared_at' => 'datetime']);

        return $resource;
    }

    public function test_recurso_privado_solo_lo_ve_su_titular(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();

        $id = $this->pilotRow((int) $ana->id, null, 'Efectivo personal', $household->id);
        $resource = $this->recurso($id);

        $this->assertTrue($resource->isVisibleTo($ana), 'El titular ve su cuenta privada.');
        $this->assertFalse($resource->isVisibleTo($luis), 'Otro integrante no la ve, aunque sea admin.');
        $this->assertSame(Visibility::Private, $resource->visibilityStatus());
    }

    public function test_recurso_compartido_lo_ven_los_integrantes_activos(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();
        $ajeno = User::factory()->create();

        $household->users()->attach($ajeno, ['role' => 'member', 'deactivated_at' => now()]);

        $id = $this->pilotRow((int) $ana->id, now()->format('Y-m-d H:i:s'), 'Cuenta compartida', $household->id);
        $resource = $this->recurso($id);

        $this->assertTrue($resource->isVisibleTo($ana));
        $this->assertTrue($resource->isVisibleTo($luis), 'Los integrantes activos ven lo compartido.');
        $this->assertFalse($resource->isVisibleTo($ajeno), 'Una membresía inactiva no conserva acceso.');
        $this->assertSame(Visibility::Shared, $resource->visibilityStatus());
    }

    public function test_recurso_del_hogar_sin_titular_es_visible_para_todos(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();

        $id = $this->pilotRow(null, null, 'Caja chica del hogar', $household->id);
        $resource = $this->recurso($id);

        $this->assertTrue($resource->isVisibleTo($ana));
        $this->assertTrue($resource->isVisibleTo($luis));
        $this->assertSame(Visibility::Private, $resource->visibilityStatus(), 'Sin compartición es privada-de-hogar, visible igualmente.');
    }

    public function test_al_volver_privada_aun_integrante_activo_pierde_el_acceso(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();

        // Ana compartió y después ocultó: la vigencia actual rige (ADR-0002).
        $id = $this->pilotRow((int) $ana->id, '2026-10-01 12:00:00', 'Cuenta regresada a privada', $household->id);
        $resource = $this->recurso($id);

        $this->assertTrue($resource->isVisibleTo($ana));
        $this->assertSame(Visibility::Shared, $resource->visibilityStatus());

        // Titular revierte la compartición.
        DB::table('pilot_resources')->where('id', $id)->update(['shared_at' => null]);
        $resource->refresh();

        $this->assertFalse($resource->isVisibleTo($luis), 'Al ocultarla, otros dejan de verla de inmediato.');
        $this->assertFalse(DB::table('pilot_resources')->where('id', $id)->value('label') === null, 'El histórico del registro no se borra (RG-10).');
    }

    public function test_scope_visible_to_filtra_listados_sin_filtracion(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();

        $propiaPrivada = $this->pilotRow((int) $ana->id, null, 'Efectivo', $household->id);
        $propiaCompartida = $this->pilotRow((int) $ana->id, now()->format('Y-m-d H:i:s'), 'Cuenta compartida', $household->id);
        $delHogar = $this->pilotRow(null, null, 'Caja del hogar', $household->id);
        $this->pilotRow((int) $luis->id, null, 'Privada de Luis', $household->id);
        $compartidaDeLuis = $this->pilotRow((int) $luis->id, now()->format('Y-m-d H:i:s'), 'Banco de Luis compartido', $household->id);
        $otroHogar = Household::factory()->create();
        $this->pilotRow(null, null, 'Caja de otro hogar', $otroHogar->id);

        $class = $this->modelClass::class;

        /** @var Builder $query */
        $query = $class::query()->visibleTo($ana, $household);
        $vistoA = $query->pluck('id')->all();

        // Ana ve lo suyo, lo compartido y los recursos del hogar; no la privada
        // de Luis ni recursos de otro hogar (RG-01).
        $this->assertEqualsCanonicalizing([$propiaPrivada, $propiaCompartida, $delHogar, $compartidaDeLuis], $vistoA);
    }

    public function test_scope_visible_to_para_luis(): void
    {
        [$household, $ana, $luis] = $this->householdWithTwoActiveMembers();

        $this->pilotRow((int) $ana->id, null, 'Efectivo de Ana', $household->id);
        $compartidaDeAna = $this->pilotRow((int) $ana->id, now()->format('Y-m-d H:i:s'), 'Cuenta de Ana compartida', $household->id);
        $delHogar = $this->pilotRow(null, null, 'Caja del hogar', $household->id);
        $propia = $this->pilotRow((int) $luis->id, null, 'Efectivo de Luis', $household->id);

        $class = $this->modelClass::class;

        $this->assertEqualsCanonicalizing(
            [$compartidaDeAna, $delHogar, $propia],
            $class::query()->visibleTo($luis, $household)->pluck('id')->all(),
            'Luis ve la compartida de Ana, los recursos del hogar y los suyos.'
        );
    }

    public function test_scope_visible_to_no_devuelve_nada_a_una_membresia_desactivada_o_ajena(): void
    {
        [$household, $ana] = $this->householdWithTwoActiveMembers();
        $inactiva = User::factory()->create();
        $ajena = User::factory()->create();
        $household->users()->attach($inactiva, ['role' => 'member', 'deactivated_at' => now()]);
        $this->pilotRow((int) $ana->id, now()->format('Y-m-d H:i:s'), 'Cuenta compartida', $household->id);

        $class = $this->modelClass::class;

        $this->assertSame([], $class::query()->visibleTo($inactiva, $household)->pluck('id')->all());
        $this->assertSame([], $class::query()->visibleTo($ajena, $household)->pluck('id')->all());
    }
}
