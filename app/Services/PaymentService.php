<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;

class PaymentService
{
    /**
     * Procesa un pago simulado (mock).
     * En producción: integrar con MercadoPago o Stripe SDK aquí.
     */
    public function processPayment(Transaction $transaction, array $paymentData): Payment
    {
        // Mock: siempre aprueba en entorno local/testing
        $approved = config('app.env') !== 'production' || $this->callGateway($paymentData);

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'provider' => $paymentData['provider'] ?? 'mock',
            'provider_payment_id' => $approved ? 'MOCK-'.strtoupper(uniqid()) : null,
            'amount' => $transaction->amount,
            'status' => $approved ? 'completed' : 'failed',
            'paid_at' => $approved ? now() : null,
        ]);

        if ($approved) {
            $transaction->update(['status' => 'paid']);
        }

        return $payment;
    }

    private function callGateway(array $data): bool
    {
        // TODO: implementar integración real con MercadoPago o Stripe
        return true;
    }
}
