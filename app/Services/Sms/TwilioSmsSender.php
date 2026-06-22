<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioSmsSender implements SmsSender
{
    public function send(string $phone, string $message): void
    {
        $sid = config('sms.twilio.sid');
        $token = config('sms.twilio.token');
        $from = config('sms.twilio.from');

        if (! $sid || ! $token || ! $from) {
            throw new RuntimeException('Twilio no está configurado. Revisa TWILIO_* en .env');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $phone,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo enviar el SMS: '.$response->body());
        }
    }
}
