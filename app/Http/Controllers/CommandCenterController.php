<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Task;
use App\Models\Tenant;
use App\Support\NotificationDispatcher;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandCenterController extends Controller
{
    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Musteri basariyla olusturuldu.');
    }

    public function storeReceivable(StoreReceivableRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'paid') {
            $data['paid_at'] = now();
        }

        $recurrenceType = $data['recurrence_type'] ?? 'once';
        $installmentCount = (int) ($data['installment_count'] ?? 1);

        if ($recurrenceType !== 'once' && $installmentCount >= 2) {
            $baseDate = Carbon::parse($data['due_date']);

            // Create first installment
            $parent = Receivable::create(array_merge($data, [
                'recurrence_type' => $recurrenceType,
                'installment_count' => $installmentCount,
                'installment_number' => 1,
                'parent_id' => null,
            ]));

            // Create remaining installments
            for ($i = 2; $i <= $installmentCount; $i++) {
                $nextDate = $this->advanceDate($baseDate->copy(), $recurrenceType, $i - 1);

                Receivable::create([
                    'tenant_id' => $parent->tenant_id,
                    'customer_id' => $data['customer_id'],
                    'amount' => $data['amount'],
                    'due_date' => $nextDate,
                    'status' => 'upcoming',
                    'description' => $data['description'] ?? null,
                    'recurrence_type' => $recurrenceType,
                    'installment_count' => $installmentCount,
                    'installment_number' => $i,
                    'parent_id' => $parent->id,
                ]);
            }

            return redirect()->route('dashboard', $this->preserveQuery($request))
                ->with('success', "{$installmentCount} taksitli alacak basariyla olusturuldu.");
        }

        Receivable::create($data);

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Alacak basariyla olusturuldu.');
    }

    public function storeTask(StoreTaskRequest $request): RedirectResponse
    {
        Task::create($request->validated());

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Gorev basariyla olusturuldu.');
    }

    public function updateReceivable(UpdateReceivableRequest $request, Receivable $receivable): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'paid') {
            $data['paid_at'] = $receivable->paid_at ?? now();
        } else {
            $data['paid_at'] = null;
        }

        $receivable->update($data);

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Alacak basariyla guncellendi.');
    }

    public function markReceivablePaid(Receivable $receivable): RedirectResponse
    {
        $receivable->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Alacak odendi olarak isaretlendi.');
    }

    public function sendReceivableNotification(Receivable $receivable): RedirectResponse
    {
        $result = NotificationDispatcher::dispatchForReceivable($receivable);

        if ($result['sms'] || $result['mail']) {
            $parts = [];
            if ($result['sms']) {
                $parts[] = 'SMS';
            }
            if ($result['mail']) {
                $parts[] = 'Mail';
            }

            return redirect()->route('dashboard')
                ->with('success', implode(' + ', $parts).' bildirimi gonderildi.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Bildirim gonderilemedi. Daha once gonderilmis olabilir veya musteri bilgileri eksik.');
    }

    public function runDueNotifications(): RedirectResponse
    {
        Artisan::call('notifications:send-due');
        $output = trim(Artisan::output());

        return redirect()->route('dashboard')
            ->with('success', $output ?: 'Bildirimler basariyla calistirildi.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_time' => 'required|date_format:H:i',
            'sms_enabled' => 'boolean',
            'mail_enabled' => 'boolean',
        ]);

        $tenant = Tenant::findOrFail(TenantContext::tenantId());
        $tenant->update([
            'notification_time' => $data['notification_time'],
            'sms_enabled' => $request->boolean('sms_enabled'),
            'mail_enabled' => $request->boolean('mail_enabled'),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Bildirim ayarlari guncellendi.');
    }

    protected function advanceDate(Carbon $base, string $type, int $steps): Carbon
    {
        return match ($type) {
            'weekly' => $base->addWeeks($steps),
            'biweekly' => $base->addWeeks($steps * 2),
            'monthly' => $base->addMonths($steps),
            'yearly' => $base->addYears($steps),
            default => $base->addMonths($steps),
        };
    }

    protected function preserveQuery($request): array
    {
        return array_filter([
            'tab' => $request->query('tab'),
            'q' => $request->query('q'),
            'customer_id' => $request->query('customer_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);
    }
}
