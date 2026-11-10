<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** EP03-08: la auditoría registra actor, hogar, entidad, acción y origen. */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_cambio_de_rol_con_actor_y_entidad(): void
    {
        [$household, $admin, $member] = $this->householdWithAdminAndMember();

        $this->actingAs($admin)
            ->post(route('household.members.role', $member), ['role' => 'viewer'])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'household_id' => $household->id,
            'actor_user_id' => $admin->id,
            'action' => ActivityLog::UPDATED_MEMBER_ROLE,
            'subject_type' => User::class,
            'subject_id' => $member->id,
            'origin' => 'household.members.role',
        ]);
        $this->assertSame('viewer', ActivityLog::sole()->metadata['role']);
    }

    public function test_registra_invitacion_sin_exponer_correo_ni_token_en_metadatos(): void
    {
        Mail::fake();
        [$household, $admin] = $this->householdWithAdminAndMember();

        $this->actingAs($admin)
            ->post(route('household.invitations.store'), [
                'email' => 'privado@example.com',
                'role' => 'member',
            ])
            ->assertRedirect();

        $log = ActivityLog::sole();

        $this->assertSame($household->id, $log->household_id);
        $this->assertSame($admin->id, $log->actor_user_id);
        $this->assertSame(ActivityLog::CREATED_INVITATION, $log->action);
        $this->assertSame(['role' => 'member'], $log->metadata);
    }

    public function test_actividad_muestra_solo_registros_propios_a_integrante(): void
    {
        [$household, $admin, $member] = $this->householdWithAdminAndMember();
        ActivityLog::create(['household_id' => $household->id, 'actor_user_id' => $admin->id, 'action' => ActivityLog::CREATED_INVITATION, 'subject_type' => User::class, 'subject_id' => $admin->id, 'created_at' => now()]);
        ActivityLog::create(['household_id' => $household->id, 'actor_user_id' => $member->id, 'action' => ActivityLog::UPDATED_MEMBER_ROLE, 'subject_type' => User::class, 'subject_id' => $member->id, 'created_at' => now()]);

        $this->actingAs($member)->get(route('household.index', ['tab' => 'actividad']))
            ->assertOk()
            ->assertSee('Actividad del hogar')
            ->assertSee('Se actualizó un rol')
            ->assertDontSee('Se invitó a una persona');
    }

    public function test_pestana_permisos_muestra_la_regla_de_privacidad_aprobada(): void
    {
        [, $admin] = $this->householdWithAdminAndMember();

        $this->actingAs($admin)->get(route('household.index', ['tab' => 'permisos']))
            ->assertOk()
            ->assertSee('Permisos y privacidad')
            ->assertSee('Administrar no da acceso a lo privado')
            ->assertSee('Tus cuentas personales');
    }

    /** @return array{0: Household, 1: User, 2: User} */
    private function householdWithAdminAndMember(): array
    {
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $household = Household::factory()->create(['created_by' => $admin->id]);
        $household->users()->attach($admin, ['role' => 'admin']);
        $household->users()->attach($member, ['role' => 'member']);

        return [$household, $admin, $member];
    }
}
