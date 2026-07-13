@php
    $tabs = [
        ['route' => 'sms-logs.index', 'active' => request()->routeIs('sms-logs.*'), 'label' => 'SMS'],
        ['route' => 'mail-logs.index', 'active' => request()->routeIs('mail-logs.*'), 'label' => 'E-posta'],
    ];
@endphp

<div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition
                  {{ $tab['active']
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
