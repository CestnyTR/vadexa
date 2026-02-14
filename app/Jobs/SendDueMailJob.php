<?php

namespace App\Jobs;

use App\Models\MailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDueMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public int $timeout = 30;

    public function __construct(
        public int $tenantId,
        public ?int $customerId,
        public string $email,
        public string $type,
        public string $customerName,
        public string $amount,
        public string $dueDate,
    ) {}

    public function handle(): void
    {
        $subject = $this->buildSubject();
        $body = $this->buildBody();

        try {
            Mail::send('emails.receivable_due', [
                'customerName' => $this->customerName,
                'amount' => $this->amount,
                'dueDate' => $this->dueDate,
                'type' => $this->type,
                'body' => $body,
            ], function ($message) use ($subject) {
                $message->to($this->email)
                    ->subject($subject);
            });

            MailLog::query()->create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $this->customerId,
                'email' => $this->email,
                'type' => $this->type,
                'subject' => $subject,
                'body' => $body,
                'status' => 'success',
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            MailLog::query()->create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $this->customerId,
                'email' => $this->email,
                'type' => $this->type,
                'subject' => $subject,
                'body' => $body,
                'status' => 'failed',
                'error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
        }
    }

    protected function buildSubject(): string
    {
        return match ($this->type) {
            'upcoming' => "Odeme Hatirlatmasi - {$this->dueDate}",
            'due_today' => "Bugun Vadesi Gelen Odeme - {$this->amount} TL",
            'overdue' => "Geciken Odeme Bildirimi - {$this->amount} TL",
            default => "Odeme Bildirimi",
        };
    }

    protected function buildBody(): string
    {
        return match ($this->type) {
            'upcoming' => "{$this->customerName}, {$this->dueDate} tarihli {$this->amount} TL odemeniz yaklesmaktadir.",
            'due_today' => "{$this->customerName}, {$this->amount} TL odemenizin vadesi bugundur ({$this->dueDate}).",
            'overdue' => "{$this->customerName}, {$this->dueDate} vadeli {$this->amount} TL odemeniz gecikmededir.",
            default => "{$this->customerName}, {$this->amount} TL odemeniz icin bilgilendirme.",
        };
    }
}
