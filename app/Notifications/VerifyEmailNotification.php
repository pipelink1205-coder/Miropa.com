<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        $brand = config('brand.name', 'Mi Ropa');

        return (new MailMessage)
            ->subject("Verifica tu correo en {$brand}")
            ->greeting('¡Hola!')
            ->line("Gracias por registrarte en {$brand}.")
            ->line('Confirma tu dirección de correo para publicar, comprar y recibir notificaciones.')
            ->action('Verificar correo', $url)
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation("— El equipo de {$brand}");
    }
}
