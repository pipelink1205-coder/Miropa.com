<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCodeNotification extends Notification
{
    public function __construct(
        public readonly string $code,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brand = config('brand.name', 'Mi Ropa');
        $minutes = config('mail.verification.code_expires_minutes', 10);

        return (new MailMessage)
            ->subject("Tu código de verificación en {$brand}")
            ->greeting('¡Hola!')
            ->line("Gracias por registrarte en {$brand}.")
            ->line('Usa este código para confirmar tu correo:')
            ->line("**{$this->code}**")
            ->line("El código expira en {$minutes} minutos.")
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation("— El equipo de {$brand}");
    }
}
