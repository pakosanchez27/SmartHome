<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Notifications\HouseholdInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HouseholdInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function householdForAdmin(): array
    {
        $admin = User::factory()->create();
        $household = Household::factory()->create(['created_by' => $admin->id]);
        $household->users()->attach($admin, ['role' => 'admin']);

        return [$admin, $household];
    }

    /** Cierra la sesión de pruebas para simular al invitado sin cuenta. */
    private function invitadoSinCuenta(): void
    {
        $this->app['auth']->guard('web')->logout();
    }

    /** Envía la invitación con el flujo de interfaz y devuelve [rawToken, invitation]. */
    private function invite(User $admin, string $email = 'maria@example.com', string $role = 'viewer'): array
    {
        Mail::fake();

        $this->actingAs($admin)->post(route('household.invitations.store'), [
            'email' => $email,
            'role' => $role,
            'message' => '¡Hola! Te invitamos a organizar nuestro hogar juntos.',
        ])->assertRedirect();

        $invitation = HouseholdInvitation::query()->where('email', $email)->latest('id')->firstOrFail();

        $rawToken = null;
        Mail::assertSent(HouseholdInvitationNotification::class, function (HouseholdInvitationNotification $mail) use (&$rawToken) {
            $rawToken = $mail->rawToken;

            return true;
        });

        return [$rawToken, $invitation];
    }

    private function issueDirect(User $inviter, string $email, string $role, string $status = 'pending', ?\DateTimeInterface $expires = null): array
    {
        [$rawToken, $tokenHash] = HouseholdInvitation::issueToken();

        $invitation = HouseholdInvitation::create([
            'household_id' => $inviter->households()->first()->id,
            'invited_by' => $inviter->id,
            'email' => $email,
            'role' => $role,
            'token_hash' => $tokenHash,
            'status' => $status,
            'expires_at' => $expires ?? now()->addDays(HouseholdInvitation::EXPIRES_IN_DAYS),
        ]);

        return [$rawToken, $invitation];
    }

    public function test_pagina_mi_hogar_para_el_administrador(): void
    {
        [$admin, $household] = $this->householdForAdmin();

        $this->actingAs($admin)->get(route('household.index'))
            ->assertOk()
            ->assertSee('Personas que hacen hogar')
            ->assertSee('Invitar integrante');

        $this->assertSame($household->id, $admin->households()->first()->id);
    }

    public function test_pagina_mi_hogar_para_miembro_no_muestra_el_boton_de_invitar(): void
    {
        [$admin, $household] = $this->householdForAdmin();
        $member = User::factory()->create();
        $household->users()->attach($member, ['role' => 'member']);

        $this->actingAs($member)->get(route('household.index'))
            ->assertOk()
            ->assertDontSee('Invitar integrante');
    }

    public function test_usuario_sin_hogar_ve_estado_vacio_orientado_a_invitacion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('household.index'))
            ->assertOk()
            ->assertSee('Todavía no perteneces a un hogar');
    }

    public function test_el_administrador_emite_invitacion_con_correo_y_vigencia(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin);

        $this->assertDatabaseHas('household_invitations', [
            'email' => 'maria@example.com', 'role' => 'viewer', 'status' => 'pending', 'invited_by' => $admin->id,
        ]);
        $this->assertSame(64, strlen($invitation->token_hash));
        $this->assertNotSame($rawToken, $invitation->token_hash, 'La base guarda solo el hash.');
        $this->assertTrue($invitation->isUsable());
        $this->assertTrue($invitation->expires_at->isFuture());

        Mail::assertSent(HouseholdInvitationNotification::class, 1);
    }

    public function test_no_permite_dos_invitaciones_pendientes_del_mismo_correo(): void
    {
        [$admin] = $this->householdForAdmin();
        $this->invite($admin);

        Mail::fake();
        $this->actingAs($admin)->post(route('household.invitations.store'), [
            'email' => 'maria@example.com', 'role' => 'viewer',   // viewer = Consulta / integrante = member
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, HouseholdInvitation::where('email', 'maria@example.com')->count());
    }

    public function test_no_permite_invitar_un_correo_que_ya_es_integrante(): void
    {
        [$admin, $household] = $this->householdForAdmin();
        $luis = User::factory()->create(['email' => 'luis@example.com']);
        $household->users()->attach($luis, ['role' => 'member']);

        Mail::fake();
        $this->actingAs($admin)->post(route('household.invitations.store'), [
            'email' => 'luis@example.com', 'role' => 'viewer',
        ])->assertSessionHasErrors('email');

        $this->assertSame(0, HouseholdInvitation::count());
    }

    public function test_rol_invalido_o_mensaje_largo_no_crea_invitaciones(): void
    {
        [$admin] = $this->householdForAdmin();

        $this->actingAs($admin)->post(route('household.invitations.store'), [
            'email' => 'maria@example.com', 'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->actingAs($admin)->post(route('household.invitations.store'), [
            'email' => 'maria@example.com', 'role' => 'viewer', 'message' => str_repeat('a', 301),
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, HouseholdInvitation::count());
    }

    public function test_miembro_o_consulta_no_pueden_invitar(): void
    {
        [$admin, $household] = $this->householdForAdmin();
        $member = User::factory()->create();
        $household->users()->attach($member, ['role' => 'member']);

        Mail::fake();
        $this->actingAs($member)->post(route('household.invitations.store'), [
            'email' => 'maria@example.com', 'role' => 'viewer',
        ])->assertForbidden();

        Mail::assertNothingSent();
        $this->assertSame(0, HouseholdInvitation::count());
    }

    public function test_el_invitado_ve_la_pagina_publica_sin_cuenta(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin);
        $this->invitadoSinCuenta();

        $response = $this->get(route('invitation.show', $rawToken));
        $response->assertOk()
            ->assertSee('Revisa tu invitación')
            ->assertSee($invitation->household->name)
            ->assertSee($invitation->roleLabel())
            ->assertSee('Crea tu cuenta con ese correo');

        $this->assertSame($rawToken, session('invitation_token'), 'La sesión del invitado retiene el enlace.');
        $this->assertTrue(session()->has('url.intended'), 'Tras iniciar sesión debe regresar a la invitación.');
    }

    public function test_aceptar_requiere_el_correo_de_la_invitacion(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin);
        $otra = User::factory()->create(['email' => 'otra@example.com']);

        $this->actingAs($otra)
            ->post(route('invitation.accept', $rawToken))
            ->assertForbidden();

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'pending']);
        $this->assertDatabaseMissing('household_user', ['household_id' => $invitation->household_id, 'user_id' => $otra->id]);
    }

    public function test_aceptar_el_correo_correcto_crea_membresia_y_no_duplica(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin, 'maria@example.com', 'member');
        $maria = User::factory()->create(['email' => 'maria@example.com']);

        $response = $this->actingAs($maria)->post(route('invitation.accept', $rawToken));

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('household_user', [
            'household_id' => $invitation->household_id, 'user_id' => $maria->id, 'role' => 'member',
        ]);
        $this->assertDatabaseHas('household_invitations', [
            'id' => $invitation->id, 'status' => 'accepted', 'accepted_by' => $maria->id,
        ]);

        // El correo de la administradora activo sigue con el rol original; el hogar queda en sesión.
        $this->assertSame('member', $maria->roleIn($invitation->household_id));
        $this->assertSame($invitation->household_id, $response->getSession()->get('current_household_id'));

        // El token no se reutiliza: nuevo intento no agrega integrantes.
        $this->post(route('invitation.accept', $rawToken))
            ->assertRedirect(route('invitation.show', $rawToken))
            ->assertSessionHasErrors('invitacion');
        $this->assertSame(2, $invitation->household->refresh()->users()->count());
        $this->assertDatabaseCount('household_user', 2);
    }

    public function test_invitacion_vencida_no_se_acepta_ni_se_rechaza(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->issueDirect($admin, 'maria@example.com', 'viewer', expires: now()->subDay());
        $maria = User::factory()->create(['email' => 'maria@example.com']);

        $this->get(route('invitation.show', $rawToken))->assertOk()->assertSee('Invitación vencida');

        $this->actingAs($maria)->post(route('invitation.accept', $rawToken))
            ->assertRedirect(route('invitation.show', $rawToken))
            ->assertSessionHasErrors('invitacion');

        $this->post(route('invitation.reject', $rawToken))
            ->assertRedirect(route('invitation.show', $rawToken))
            ->assertSessionHasErrors('invitacion');

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'pending']);
    }

    public function test_invitacion_rechazada_por_el_invitado(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin);

        $this->post(route('invitation.reject', $rawToken))->assertRedirect(route('home'));

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'rejected']);
    }

    public function test_invitacion_cancelada_se_muestra_y_no_acepta(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->issueDirect($admin, 'maria@example.com', 'viewer', status: 'revoked');
        $maria = User::factory()->create(['email' => 'maria@example.com']);

        $this->get(route('invitation.show', $rawToken))->assertOk()->assertSee('Invitación cancelada');

        $this->actingAs($maria)->post(route('invitation.accept', $rawToken))
            ->assertRedirect(route('invitation.show', $rawToken))
            ->assertSessionHasErrors('invitacion');
    }

    public function test_reenviar_renueva_el_token_y_anula_el_anterior(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin);

        Mail::fake();
        $this->actingAs($admin)->post(route('household.invitations.resend', $invitation))->assertRedirect();

        Mail::assertSent(HouseholdInvitationNotification::class, 1);

        $invitation->refresh();
        $this->assertNotSame(hash('sha256', $rawToken), $invitation->token_hash, 'El enlace anterior dejó de funcionar.');
        $this->assertTrue($invitation->expires_at->isFuture(), 'Reenviar renueva la vigencia.');
    }

    public function test_reenviar_solo_para_pendiente_y_solo_admin(): void
    {
        [$admin, $household] = $this->householdForAdmin();
        $member = User::factory()->create();
        $household->users()->attach($member, ['role' => 'member']);
        [$rawToken, $invitation] = $this->issueDirect($admin, 'maria@example.com', 'viewer', status: 'rejected');

        $this->actingAs($member)->post(route('household.invitations.resend', $invitation))->assertForbidden();

        $this->actingAs($admin)->post(route('household.invitations.resend', $invitation))
            ->assertSessionHasErrors('invitaciones');
    }

    public function test_cancelar_inactiva_solo_pendientes(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->issueDirect($admin, 'maria@example.com', 'viewer');

        $this->actingAs($admin)->post(route('household.invitations.cancel', $invitation))->assertRedirect();

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'revoked']);

        // Cancelar algo ya revocado no rompe; el estado permanece.
        $fresh = $invitation->fresh();
        $this->assertSame('revoked', $fresh->status);
    }

    public function test_cancelar_requiere_admin(): void
    {
        [$admin, $household] = $this->householdForAdmin();
        $member = User::factory()->create();
        $household->users()->attach($member, ['role' => 'member']);
        [$rawToken, $invitation] = $this->issueDirect($admin, 'maria@example.com', 'viewer');

        $this->actingAs($member)->post(route('household.invitations.cancel', $invitation))->assertForbidden();

        $this->assertDatabaseHas('household_invitations', ['id' => $invitation->id, 'status' => 'pending']);
    }

    public function test_registro_desde_el_enlace_retoma_la_invitacion(): void
    {
        [$admin] = $this->householdForAdmin();
        [$rawToken, $invitation] = $this->invite($admin, 'maria@example.com', 'member');

        // 1. El invitado abre el enlace sin cuenta → la sesión retiene el token.
        $this->invitadoSinCuenta();
        $this->get(route('invitation.show', $rawToken))->assertOk();

        // 2. Tras registrarse, el flujo regresa a la invitación (puente A, documentado).
        $response = $this->post(route('household.register.store'), [
            'name' => 'María López',
            'email' => 'maria@example.com',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
            'household_name' => 'Casa María',
            'timezone' => 'America/Mexico_City',
        ]);

        $response->assertRedirect(route('invitation.show', $rawToken));

        // 3. Acepta: queda como integrante en el hogar ajeno y como admin en el suyo.
        $maria = User::where('email', 'maria@example.com')->firstOrFail();
        $this->actingAs($maria)->post(route('invitation.accept', $rawToken))->assertRedirect(route('home'));

        $this->assertSame('member', $maria->roleIn($invitation->household_id));
        $hogarPropio = $maria->households()->wherePivot('role', 'admin')->first();
        $this->assertNotNull($hogarPropio, 'Mantiene el hogar que creó.');
        $this->assertNotSame($hogarPropio->id, $invitation->household_id);
    }
}
