<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Receivable;
use App\Services\CustomerService;
use App\Services\NotificationService;
use App\Services\ReceivableService;
use App\Services\TaskService;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandCenterController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
        private ReceivableService $receivableService,
        private TaskService $taskService,
        private NotificationService $notificationService,
        private TenantService $tenantService,
    ) {}

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customerService->create($request->validated());

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Musteri basariyla olusturuldu.');
    }

    public function storeReceivable(StoreReceivableRequest $request): RedirectResponse
    {
        $result = $this->receivableService->createWithInstallments($request->validated());

        $message = $result['receivables_created'] > 1
            ? "{$result['receivables_created']} taksitli alacak basariyla olusturuldu."
            : 'Alacak basariyla olusturuldu.';

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', $message);
    }

    public function storeTask(StoreTaskRequest $request): RedirectResponse
    {
        $this->taskService->create($request->validated());

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Gorev basariyla olusturuldu.');
    }

    public function updateReceivable(UpdateReceivableRequest $request, Receivable $receivable): RedirectResponse
    {
        $this->receivableService->update($receivable, $request->validated());

        return redirect()->route('dashboard', $this->preserveQuery($request))
            ->with('success', 'Alacak basariyla guncellendi.');
    }

    public function markReceivablePaid(Receivable $receivable): RedirectResponse
    {
        $this->receivableService->markPaid($receivable);

        return redirect()->route('dashboard')
            ->with('success', 'Alacak odendi olarak isaretlendi.');
    }

    public function sendReceivableNotification(Receivable $receivable): RedirectResponse
    {
        $result = $this->notificationService->dispatchForReceivable($receivable);

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

        $this->tenantService->updateSettings([
            'notification_time' => $data['notification_time'],
            'sms_enabled' => $request->boolean('sms_enabled'),
            'mail_enabled' => $request->boolean('mail_enabled'),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Bildirim ayarlari guncellendi.');
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
