@props(['status', 'label' => null])

@php
    // Centralised status → label + colour map for receivables, tasks and payments.
    $map = [
        // Receivable
        'upcoming'  => ['Yaklaşan',  'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300'],
        'due_today' => ['Bugün',     'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'],
        'overdue'   => ['Gecikmiş',  'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        'paid'      => ['Ödendi',    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
        // Task
        'todo'      => ['Yapılacak', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
        'doing'     => ['Yapılıyor', 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'],
        'done'      => ['Bitti',     'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
        // Payment
        'pending'   => ['Bekliyor',  'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'],
        'failed'    => ['Başarısız', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        // SMS / e-mail delivery status
        'queued'    => ['Kuyrukta',  'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
        'sent'      => ['Gönderildi','bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
        'success'   => ['Başarılı',  'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
    ];

    [$defaultLabel, $classes] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $label ?? $defaultLabel }}
</span>
