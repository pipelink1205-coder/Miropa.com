<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $brand = config('brand.name', 'Mi Ropa');
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject("Restablecer contraseña — {$brand}")
            ->greeting('¡Hola!')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace expira en {$expire} minutos.")
            ->line('Si no solicitaste el cambio, ignora este correo; tu contraseña no se modificará.')
            ->salutation("— El equipo de {$brand}");
    }
}
