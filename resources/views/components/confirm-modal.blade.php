@props([
    'name',
    'title' => 'Onay',
    'message' => 'Bu islemi gerceklestirmek istediginize emin misiniz?',
    'action' => '',
    'method' => 'POST',
    'confirmText' => 'Onayla',
    'confirmColor' => 'indigo',
])

@php
$btnClasses = match($confirmColor) {
    'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'green', 'emerald' => 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500',
    default => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
};
@endphp

<div
    x-data="{ show: false, action: '{{ $action }}' }"
    x-on:open-confirm.window="if ($event.detail.name == '{{ $name }}') { show = true; if ($event.detail.action) action = $event.detail.action; }"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"
        @click="show = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-md sm:mx-auto"
    >
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
            <button @click="show = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                İptal
            </button>
            <form :action="action" method="POST">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif
                {{ $slot }}
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition {{ $btnClasses }}">
                    {{ $confirmText }}
                </button>
            </form>
        </div>
    </div>
</div>
