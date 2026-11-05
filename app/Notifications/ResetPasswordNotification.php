<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new MailMessage)
            ->subject('Recupera tu acceso a SmartHome')
            ->greeting('Hola')
            ->line('Recibimos una solicitud para recuperar tu contraseña.')
            ->action('Crear una nueva contraseña', $url)
            ->line('El enlace vence en '.config('auth.passwords.users.expire').' minutos. Si no solicitaste este cambio, ignora este mensaje.');
    }
}
