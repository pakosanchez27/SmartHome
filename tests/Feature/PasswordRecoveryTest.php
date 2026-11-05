<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitar_enlace_no_revela_si_existe_el_correo(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $message = 'Si el correo está registrado, recibirás un enlace para recuperar tu acceso.';

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status', $message);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $this->post(route('password.email'), ['email' => 'ausente@ejemplo.mx'])->assertSessionHas('status', $message);
    }

    public function test_token_valido_cambia_contrasena_y_no_puede_reutilizarse(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        $payload = ['token' => $token, 'email' => $user->email, 'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234'];

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();
        $this->post(route('password.update'), $payload)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NuevaClave1234', $user->fresh()->password));
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_rechaza_contrasena_debil(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => '123', 'password_confirmation' => '123'])
            ->assertSessionHasErrors('password');
    }

    public function test_rechaza_un_enlace_vencido(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuevaClave1234',
            'password_confirmation' => 'NuevaClave1234',
        ])->assertSessionHasErrors('email');
    }
}
