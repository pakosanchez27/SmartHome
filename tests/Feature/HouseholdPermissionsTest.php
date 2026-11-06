<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * EP03-05: matriz de permisos por rol (docs/ROLES_Y_PRIVACIDAD.md §1–§2).
 *
 * Ocultar botones no es autorización: cada capacidad se valida con Policies
 * en servidor y se prueba desde HTTP o Gate, nunca desde la interfaz.
 */
class HouseholdPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function addMember(Household $household, string $role, ?\DateTimeInterface $deactivatedAt = null): User
    {
        $member = User::factory()->create();
        $household->users()->attach($member, ['role' => $role, 'deactivated_at' => $deactivatedAt]);

        return $member;
    }

    // ── Matriz de roles (Policy Household) ──────────────────────────────

    public function test_admin_ve_todo_del_hogar(): void
    {
        [$household, $admin] = $this->pairWithAdmin();

        $this->actingAs($admin);

        $this->assertTrue($admin->can('view', $household));
        $this->assertTrue($admin->can('operate', $household));
        $this->assertTrue($admin->can('manageMembers', $household));
        $this->assertTrue($admin->can('update', $household));
    }

    public function test_integrante_opera_pero_no_administra(): void
    {
        [$household, , $member] = $this->trioForRole('member');

        $this->actingAs($member);

        $this->assertTrue($member->can('view', $household));
        $this->assertTrue($member->can('operate', $household));
        $this->assertFalse($member->can('manageMembers', $household));
        $this->assertFalse($member->can('update', $household));
    }

    public function test_consulta_solo_ve(): void
    {
        [$household, , $viewer] = $this->trioForRole('viewer');

        $this->actingAs($viewer);

        $this->assertTrue($viewer->can('view', $household));
        $this->assertFalse($viewer->can('operate', $household));
        $this->assertFalse($viewer->can('manageMembers', $household));
        $this->assertFalse($viewer->can('update', $household));
    }

    public function test_membresia_desactivada_pierde_todas_las_capacidades(): void
    {
        [$household, , $exMember] = $this->trioForRole('member', now()->subDay(), 'deactivated');

        $this->actingAs($exMember);

        $this->assertNull($exMember->roleIn($household), 'El rol activo de una membresía desactivada es nulo.');
        $this->assertFalse($exMember->can('view', $household));
        $this->assertFalse($exMember->can('operate', $household));
        $this->assertFalse($exMember->can('manageMembers', $household));
        $this->assertFalse($exMember->can('update', $household));
    }

    public function test_ajeno_al_hogar_no_puede_nada(): void
    {
        [$household] = $this->pairWithAdmin();
        $outsider = User::factory()->create();

        $this->actingAs($outsider);

        $this->assertFalse($outsider->can('view', $household));
        $this->assertFalse($outsider->can('operate', $household));
        $this->assertFalse($outsider->can('manageMembers', $household));
        $this->assertFalse($outsider->can('update', $household));
    }

    // ── Aplicación real vía HTTP ────────────────────────────────────────

    public function test_consulta_y_ajeno_no_pueden_invitar_http(): void
    {
        Mail::fake();

        [$household, $admin, $viewer] = $this->pairWithAdminAndViewer();
        $outsider = User::factory()->create();

        foreach ([$viewer, $outsider] as $actor) {
            $this->actingAs($actor)
                ->post(route('household.invitations.store'), [
                    'email' => 'nueva@example.com',
                    'role' => 'viewer',
                ])
                ->assertForbidden();
        }

        // La admin sigue pudiendo.
        $this->actingAs($admin)
            ->post(route('household.invitations.store'), [
                'email' => 'nueva@example.com',
                'role' => 'viewer',
            ])
            ->assertRedirect();

        $this->assertSame(1, HouseholdInvitation::where('household_id', $household->id)->count());
    }

    public function test_la_pagina_mi_hogar_niega_ajenos(): void
    {
        [$household] = $this->pairWithAdmin();
        $outsider = User::factory()->create();

        // Un ajeno no pertenece: se resuelve sin hogar → estado vacío, no error.
        $this->actingAs($outsider)
            ->get(route('household.index'))
            ->assertOk()
            ->assertSee('Todavía no perteneces a un hogar');
    }

    // ── IDOR: administración cruzada entre hogares ──────────────────────

    public function test_admin_de_otro_hogar_no_puede_reenviar_ni_cancelar_invitacion_ajena(): void
    {
        Mail::fake();

        // Hogar A: la admin emite una invitación.
        [$householdA, $adminA] = $this->pairWithAdmin();
        $invitation = $this->emitInvitation($adminA, 'maria@example.com');

        // Admin de otro hogar (la otra adulta con su propio hogar).
        $adminB = User::factory()->create();
        $otherHousehold = Household::factory()->create(['created_by' => $adminB->id]);
        $otherHousehold->users()->attach($adminB, ['role' => 'admin']);

        $this->actingAs($adminB)
            ->post(route('household.invitations.resend', $invitation))
            ->assertForbidden();

        $this->actingAs($adminB)
            ->post(route('household.invitations.cancel', $invitation))
            ->assertForbidden();

        // La invitación sigue intacta: ni revocada ni con token renovado.
        $this->assertDatabaseHas('household_invitations', [
            'id' => $invitation->id,
            'status' => 'pending',
            'token_hash' => $invitation->token_hash,
        ]);
    }

    public function test_integrante_no_puede_reenviar_ni_cancelar_invitaciones_de_su_hogar(): void
    {
        Mail::fake();

        [$household, , $member] = $this->trioForRole('member');
        $invitation = $this->emitInvitation($household->creator, 'maria@example.com');

        $this->actingAs($member)
            ->post(route('household.invitations.resend', $invitation))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('household.invitations.cancel', $invitation))
            ->assertForbidden();

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'pending']);
    }

    // ── Policy de aceptación de invitaciones ────────────────────────────

    public function test_policy_de_aceptacion_exige_correo_coincidente(): void
    {
        [$household] = $this->pairWithAdmin();
        $invitation = $this->emitInvitation($household->creator, 'maria@example.com');
        $otra = User::factory()->create(['email' => 'otra@example.com']);

        $this->assertFalse($otra->can('accept', $invitation), 'El correo no coincide: no puede aceptar.');

        $maria = User::factory()->create(['email' => 'Maria@Example.com']);
        $this->assertTrue($maria->can('accept', $invitation), 'El correo coincide (insensible a mayúsculas).');
    }

    public function test_policy_de_aceptacion_niega_ajenos_otros_estados_son_mensaje_no_forbidden(): void
    {
        [$household] = $this->pairWithAdmin();
        $maria = User::factory()->create(['email' => 'maria@example.com']);

        $vencida = $this->emitInvitation($household->creator, 'maria@example.com', now()->subDay());
        $cancelada = $this->emitInvitation($household->creator, 'maria@example.com', status: 'revoked');
        $rechazada = $this->emitInvitation($household->creator, 'maria@example.com', status: 'rejected');
        $usada = $this->emitInvitation($household->creator, 'maria@example.com', status: 'accepted');

        // La policy autoriza la identidad: María puede intentar usar cualquier
        // enlace dirigido a su correo. Los estados no usables los rechaza el
        // claim idempotente con un mensaje amable (probado en EP03-04).
        foreach ([$vencida, $cancelada, $rechazada, $usada] as $invitation) {
            $this->assertTrue($maria->can('accept', $invitation));
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /** @return array{0: Household, 1: User, 2: ?User} */
    private function pairWithAdmin(): array
    {
        $admin = User::factory()->create();
        $household = Household::factory()->create(['created_by' => $admin->id]);
        $household->users()->attach($admin, ['role' => 'admin']);

        return [$household, $admin, null];
    }

    /** @return array{0: Household, 1: User, 2: ?User} */
    private function pairWithAdminAndViewer(): array
    {
        [$household, $admin] = $this->pairWithAdmin();
        $viewer = $this->addMember($household, 'viewer');

        return [$household, $admin, $viewer];
    }

    /** @return array{0: Household, 1: User, 2: User} */
    private function trioForRole(string $role, ?\DateTimeInterface $deactivatedAt = null, string $kind = 'active'): array
    {
        [$household, $admin] = $this->pairWithAdmin();

        $member = $kind === 'deactivated'
            ? $this->addMember($household, $role, $deactivatedAt)
            : $this->addMember($household, $role);

        return [$household, $admin, $member];
    }

    private function emitInvitation(User $admin, string $email, ?\DateTimeInterface $expires = null, string $status = 'pending'): HouseholdInvitation
    {
        [$rawToken, $tokenHash] = HouseholdInvitation::issueToken();

        return HouseholdInvitation::create([
            'household_id' => $admin->activeHouseholds()->first()->id,
            'invited_by' => $admin->id,
            'email' => $email,
            'role' => 'viewer',
            'token_hash' => $tokenHash,
            'status' => $status,
            'expires_at' => $expires ?? now()->addDays(HouseholdInvitation::EXPIRES_IN_DAYS),
        ]);
    }
}
