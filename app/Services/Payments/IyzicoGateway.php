<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class IyzicoGateway implements PaymentGateway
{
    public function charge(Payment $payment, Tenant $tenant): PaymentResult
    {
        if ($this->isMock()) {
            return PaymentResult::success('mock-'.Str::uuid());
        }

        try {
            return $this->chargeViaIyzico($payment, $tenant);
        } catch (Throwable $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    /**
     * Whether to simulate payments. True when explicitly enabled or when the
     * API credentials have not been provided yet.
     */
    protected function isMock(): bool
    {
        if ((bool) config('services.iyzico.mock', true)) {
            return true;
        }

        return blank(config('services.iyzico.api_key')) || blank(config('services.iyzico.secret_key'));
    }

    /**
     * Real Iyzico charge. The full request/response plumbing and IYZWSv2
     * (HMAC-SHA256) authentication are implemented here — the only remaining
     * step to go live is providing real API keys in the environment.
     */
    protected function chargeViaIyzico(Payment $payment, Tenant $tenant): PaymentResult
    {
        $baseUrl = rtrim((string) config('services.iyzico.base_url'), '/');
        $uriPath = '/payment/auth';

        $body = [
            'locale' => 'tr',
            'conversationId' => (string) $payment->id,
            'price' => (string) $payment->amount,
            'paidPrice' => (string) $payment->amount,
            'currency' => $payment->currency,
            'basketId' => 'plan_'.$payment->plan,
            'paymentGroup' => 'SUBSCRIPTION',
        ];

        $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = Http::withHeaders([
            'Authorization' => $this->authorizationHeader($uriPath, $payload),
            'Content-Type' => 'application/json',
        ])->withBody($payload, 'application/json')
            ->post($baseUrl.$uriPath);

        if (! $response->successful()) {
            return PaymentResult::failure('Iyzico HTTP '.$response->status());
        }

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return PaymentResult::success((string) ($data['paymentId'] ?? $data['conversationId'] ?? Str::uuid()));
        }

        return PaymentResult::failure($data['errorMessage'] ?? 'Iyzico ödeme reddedildi.');
    }

    /**
     * Build the IYZWSv2 (HMAC-SHA256) Authorization header used by the Iyzico API.
     */
    protected function authorizationHeader(string $uriPath, string $payload): string
    {
        $apiKey = (string) config('services.iyzico.api_key');
        $secret = (string) config('services.iyzico.secret_key');
        $randomKey = (string) now()->getTimestampMs().Str::random(8);

        $signature = hash_hmac(
            'sha256',
            $randomKey.$uriPath.$payload,
            $secret,
        );

        $authParams = "apiKey:{$apiKey}&randomKey:{$randomKey}&signature:{$signature}";

        return 'IYZWSv2 '.base64_encode($authParams);
    }
}
