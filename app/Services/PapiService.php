<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PapiService
{
    public function createPaymentLink(array $data)
    {
        $payload = [
            'amount' => $data['amount'],
            'clientName' => $data['clientName'],
            'reference' => $data['reference'],
            'description' => $data['description'] ?? null,

            'successUrl' => config('services.papi.success_url'),
            'failureUrl' => config('services.papi.failure_url'),
            'notificationUrl' => config('services.papi.notification_url'),

            'validDuration' => config('services.papi.valid_duration'),

            'payerEmail' => $data['payerEmail'] ?? null,
            'payerPhone' => $data['payerPhone'] ?? null,

            'isTestMode' => config('services.papi.test_mode'),
        ];

        if (!empty($data['provider'])) {
            $payload['provider'] = $data['provider'];
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => config('services.papi.api_key'),
        ])->post(
            rtrim(config('services.papi.url'), '/') . '/payment-links',
            $payload
        );
    }
}