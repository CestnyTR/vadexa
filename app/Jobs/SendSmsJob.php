<?php

namespace App\Jobs;

use App\Models\SmsLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public int $timeout = 30;

    public function __construct(
        public int $tenantId,
        public ?int $customerId,
        public string $phone,
        public string $type,
        public string $message,
        public ?string $contextType = null,
        public ?int $contextId = null,
    ) {}

    public function handle(): void
    {
        try {
            $providerMessageId = $this->sendViaNetgsm();

            SmsLog::query()->create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $this->customerId,
                'phone' => $this->phone,
                'type' => $this->type,
                'message' => $this->message,
                'status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'context_type' => $this->contextType,
                'context_id' => $this->contextId,
            ]);
        } catch (Throwable $exception) {
            SmsLog::query()->create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $this->customerId,
                'phone' => $this->phone,
                'type' => $this->type,
                'message' => $this->message,
                'status' => 'failed',
                'error' => mb_substr($exception->getMessage(), 0, 1000),
                'context_type' => $this->contextType,
                'context_id' => $this->contextId,
            ]);
        }
    }

    protected function sendViaNetgsm(): string
    {
        if ((bool) config('services.netgsm.mock', true)) {
            return 'mock-'.Str::uuid();
        }

        $username = (string) config('services.netgsm.user_code');
        $password = (string) config('services.netgsm.password');
        $header = (string) config('services.netgsm.header');
        $url = (string) config('services.netgsm.url');

        if (blank($username) || blank($password) || blank($header)) {
            throw new RuntimeException('NetGSM credentials are missing.');
        }

        $response = Http::asForm()->post($url, [
            'usercode' => $username,
            'password' => $password,
            'msgheader' => $header,
            'gsmno' => $this->phone,
            'message' => $this->message,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('NetGSM request failed: '.$response->status());
        }

        return $this->parseNetgsmResponse($response);
    }

    protected function parseNetgsmResponse(Response $response): string
    {
        $body = trim($response->body());

        if (preg_match('/^00\s+([A-Za-z0-9_-]+)$/', $body, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^(\d{2,3})$/', $body, $matches) === 1) {
            $code = $matches[1];

            throw new RuntimeException(
                sprintf('NetGSM API error [%s]: %s', $code, $this->netgsmErrorMessage($code))
            );
        }

        throw new RuntimeException('Unexpected NetGSM response body: '.$body);
    }

    protected function netgsmErrorMessage(string $code): string
    {
        return match ($code) {
            '20' => 'Message payload is missing or malformed.',
            '30' => 'Authentication failed (invalid user code or password).',
            '40' => 'Message header is not approved/defined.',
            '50' => 'Message text or phone number is missing.',
            '51' => 'Phone number format is invalid.',
            '70' => 'Send failed.',
            '80' => 'Insufficient credit.',
            '85' => 'Daily SMS sending limit exceeded.',
            '100' => 'System error.',
            '101' => 'System busy.',
            default => 'Unknown NetGSM error code.',
        };
    }
}
