<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_exige_autenticacion(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'), ['name' => 'Invitado'])->assertRedirect(route('login'));
    }

    public function test_puede_actualizar_solo_su_nombre(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $email = $user->email;

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee($user->email)->assertDontSee('Hogar Rivera');
        $this->patch(route('profile.update'), ['name' => 'Nombre nuevo', 'email' => 'ajeno@ejemplo.mx', 'id' => $other->id])->assertSessionHasNoErrors();
        $this->assertSame('Nombre nuevo', $user->fresh()->name);
        $this->assertSame($email, $user->fresh()->email);
        $this->assertNotSame('Nombre nuevo', $other->fresh()->name);
    }

    public function test_cambiar_contrasena_requiere_la_actual_y_una_nueva_valida(): void
    {
        $user = User::factory()->create(['password' => 'Anterior12345']);
        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'incorrecta', 'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234',
        ])->assertSessionHasErrorsIn('password', 'current_password');
        $this->assertTrue(Hash::check('Anterior12345', $user->fresh()->password));

        $this->put(route('profile.password'), [
            'current_password' => 'Anterior12345', 'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NuevaClave1234', $user->fresh()->password));
    }
}
