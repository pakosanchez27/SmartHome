<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pantalla_de_acceso_se_muestra(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_un_usuario_puede_iniciar_y_cerrar_sesion(): void
    {
        $user = User::factory()->create(['password' => 'ClaveSegura123']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'ClaveSegura123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_rechaza_credenciales_invalidas_y_limita_intentos(): void
    {
        $user = User::factory()->create(['password' => 'ClaveSegura123']);
        $key = strtolower($user->email).'|127.0.0.1';
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrecta'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'ClaveSegura123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        RateLimiter::clear($key);
    }
}
