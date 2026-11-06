<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Notifications\HouseholdInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * EP03-06: cambio de rol, desactivación y retiro de integrantes.
 *
 * Copia del mockup J01: la fila propia no tiene acciones; desactivar es
 * temporal (reactivable) y retirar es definitivo (nueva invitación para
 * volver). Solo la administración del hogar gestiona integrantes (RG-01)
 * y el historial nunca se borra (RG-10).
 */
class HouseholdMembersTest extends TestCase
{
    use RefreshDatabase;

    private Household $household;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->household = Household::factory()->create(['created_by' => $this->admin->id]);
        $this->household->users()->attach($this->admin, ['role' => 'admin']);
    }

    private function addMember(string $role = 'member'): User
    {
        $member = User::factory()->create();
        $this->household->users()->attach($member, ['role' => $role]);

        return $member;
    }

    // ── Cambio de rol ───────────────────────────────────────────────────

    public function test_admin_cambia_el_rol_de_un_integrante(): void
    {
        $member = $this->addMember('member');

        $this->actingAs($this->admin)
            ->post(route('household.members.role', ['member' => $member->id]), ['role' => 'viewer'])
            ->assertRedirect();

        $this->assertSame('viewer', $member->roleIn($this->household->id));
    }

    public function test_admin_asiende_un_segundo_administrador(): void
    {
        $member = $this->addMember('member');

        $this->actingAs($this->admin)
            ->post(route('household.members.role', ['member' => $member->id]), ['role' => 'admin'])
            ->assertRedirect();

        $this->assertSame('admin', $member->roleIn($this->household->id));
        $this->assertTrue($member->can('manageMembers', $this->household));
    }

    public function test_rol_invalido_se_rechaza(): void
    {
        $member = $this->addMember();

        $this->actingAs($this->admin)
            ->post(route('household.members.role', ['member' => $member->id]), ['role' => 'owner'])
            ->assertSessionHasErrors('role');

        $this->assertSame('member', $member->roleIn($this->household->id));
    }

    public function test_no_se_puede_cambiar_el_propio_rol(): void
    {
        // Última administración, sin sustituto.
        $this->actingAs($this->admin)
            ->post(route('household.members.role', ['member' => $this->admin->id]), ['role' => 'member'])
            ->assertSessionHasErrors('integrantes');

        $this->assertSame('admin', $this->admin->roleIn($this->household->id));
    }

    public function test_integrante_no_puede_cambiar_roles(): void
    {
        $member = $this->addMember('member');
        $otro = $this->addMember('viewer');

        $this->actingAs($member)
            ->post(route('household.members.role', ['member' => $otro->id]), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertSame('viewer', $otro->roleIn($this->household->id));
    }

    public function test_admin_de_otro_hogar_no_puede_cambiar_roles_idor(): void
    {
        $member = $this->addMember('member');

        $ajeno = User::factory()->create();
        $otroHogar = Household::factory()->create(['created_by' => $ajeno->id]);
        $otroHogar->users()->attach($ajeno, ['role' => 'admin']);

        // 404 (no 403): revelar que el ID vive en otro hogar facilitaría la enumeración.
        $this->actingAs($ajeno)
            ->post(route('household.members.role', ['member' => $member->id]), ['role' => 'viewer'])
            ->assertNotFound();

        $this->assertSame('member', $member->roleIn($this->household->id));
    }

    public function test_id_ajeno_al_hogar_dara_404(): void
    {
        $ajeno = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('household.members.role', ['member' => $ajeno->id]), ['role' => 'viewer'])
            ->assertNotFound();
    }

    // ── Desactivar (suspensión temporal) ────────────────────────────────

    public function test_desactivar_suspende_el_acceso_conservando_el_rol(): void
    {
        $member = $this->addMember('member');

        $this->actingAs($this->admin)
            ->post(route('household.members.suspend', ['member' => $member->id]))
            ->assertRedirect();

        $this->assertNull($member->roleIn($this->household->id), 'Sin acceso mientras esté desactivada.');

        $pivot = $member->households()->where('household_id', $this->household->id)->first()->pivot;
        $this->assertSame('member', $pivot->role, 'El rol se conserva.');
        $this->assertNull($pivot->removed_at, 'No es un retiro definitivo.');
    }

    public function test_reactivar_restaura_acceso_y_rol(): void
    {
        $member = $this->addMember('member');
        $this->actingAs($this->admin)->post(route('household.members.suspend', ['member' => $member->id]));

        $this->actingAs($this->admin)
            ->post(route('household.members.reactivate', ['member' => $member->id]))
            ->assertRedirect();

        $this->assertSame('member', $member->roleIn($this->household->id));
    }

    public function test_reactivar_niega_retirados(): void
    {
        $member = $this->addMember('member');
        $this->actingAs($this->admin)->post(route('household.members.remove', ['member' => $member->id]));

        $this->actingAs($this->admin)
            ->post(route('household.members.reactivate', ['member' => $member->id]))
            ->assertSessionHasErrors('integrantes');

        $this->assertNull($member->roleIn($this->household->id));
    }

    public function test_desactivado_no_puede_operar_el_hogar(): void
    {
        $member = $this->addMember('member');
        $this->actingAs($this->admin)->post(route('household.members.suspend', ['member' => $member->id]));

        // Sin acceso activo: su authorization falla en operaciones del hogar.
        $this->actingAs($member)
            ->post(route('household.invitations.store'), [
                'email' => 'x@example.com', 'role' => 'viewer',
            ])
            ->assertForbidden();
    }

    // ── Retirar (revocación definitiva) ─────────────────────────────────

    public function test_retirar_revoca_el_acceso_de_forma_definitiva(): void
    {
        $member = $this->addMember('member');

        $this->actingAs($this->admin)
            ->post(route('household.members.remove', ['member' => $member->id]))
            ->assertRedirect();

        $this->assertNull($member->roleIn($this->household->id));

        $pivot = $member->households()->where('household_id', $this->household->id)->first()->pivot;
        $this->assertNotNull($pivot->removed_at);
        $this->assertNotNull($pivot->created_at, 'La membresía se conserva (RG-10, no borrado silencioso).');
    }

    public function test_no_se_puede_retirar_a_una_persona(): void
    {
        $this->actingAs($this->admin)
            ->post(route('household.members.remove', ['member' => $this->admin->id]))
            ->assertSessionHasErrors('integrantes');

        $this->assertSame('admin', $this->admin->roleIn($this->household->id));
    }

    public function test_retirado_puede_ser_reinvitado_y_aceptar_lo_devuelve(): void
    {
        $member = $this->addMember('member');
        $this->actingAs($this->admin)->post(route('household.members.remove', ['member' => $member->id]));

        // La validación de invitaciones solo bloquea correos que son integrantes activos.
        Mail::fake();
        $model = User::where('id', $member->id)->first();

        // La admin emite una nueva invitación al correo retirado.
        $this->actingAs($this->admin)
            ->post(route('household.invitations.store'), [
                'email' => $model->email, 'role' => 'member',
            ])
            ->assertRedirect();

        $invitation = HouseholdInvitation::query()->where('email', $model->email)->latest('id')->firstOrFail();
        $rawToken = null;
        Mail::assertSent(HouseholdInvitationNotification::class, function (HouseholdInvitationNotification $mail) use (&$rawToken) {
            $rawToken = $mail->rawToken;

            return true;
        });

        // Acepta con su cuenta: la nueva invitación es la única vía de regreso.
        $this->actingAs($model)
            ->post(route('invitation.accept', $rawToken))
            ->assertRedirect(route('home'));

        $this->assertSame('member', $member->roleIn($this->household->id));
        $pivot = $member->households()->where('household_id', $this->household->id)->first()->pivot;
        $this->assertNull($pivot->removed_at);
    }

    // ── Protección de la última administración ──────────────────────────

    public function test_no_queda_un_hogar_sin_administracion(): void
    {
        $segunda = User::factory()->create();
        $this->household->users()->attach($segunda, ['role' => 'admin']);

        // Se retira a la admin creadora; queda la segunda — permitido.
        $this->actingAs($segunda)
            ->post(route('household.members.remove', ['member' => $this->admin->id]))
            ->assertRedirect();

        $this->assertNull($this->admin->roleIn($this->household->id));
        $this->assertSame('admin', $segunda->roleIn($this->household->id));

        // Escenario: la última única admin ya no puede ser retirada (auto-gestión bloqueada).
        $this->actingAs($segunda)
            ->post(route('household.members.remove', ['member' => $segunda->id]))
            ->assertSessionHasErrors('integrantes');

        $this->assertSame('admin', $segunda->roleIn($this->household->id));
    }

    // ── Pantalla Mi hogar con los nuevos estados ────────────────────────

    public function test_la_pagina_muestra_acciones_y_estados_segun_mockup(): void
    {
        $member = $this->addMember('member');
        $suspendido = $this->addMember('viewer');
        $retirado = $this->addMember('viewer');

        $this->actingAs($this->admin)->post(route('household.members.suspend', ['member' => $suspendido->id]));
        $this->actingAs($this->admin)->post(route('household.members.remove', ['member' => $retirado->id]));

        $response = $this->actingAs($this->admin)->get(route('household.index'));
        $response->assertOk()
            // Botones de fila de integrantes (mockup J01)
            ->assertSee('Cambiar rol')
            ->assertSee('Desactivar')
            ->assertSee('Retirar')
            // Sin acciones en la fila propia de la última administración
            ->assertSee('Última administración · Asigna un sustituto antes de cambiar tu rol o salir.')
            // Estados de inactivos
            ->assertSee('Desactivado')
            ->assertSee('Retirado')
            // Reaccionar disponible solo para suspendidos
            ->assertSee('Reactivar');
    }
}
