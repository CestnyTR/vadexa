<x-app-layout>
    <div class="py-4" x-data>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Top Bar: Search + Quick Actions --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
                <form method="GET" action="{{ route('dashboard') }}" class="flex-1 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Müşteri adı / telefon / email..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                    </div>
                </form>

                <div class="flex items-center gap-2 flex-wrap">
                    <button @click="$dispatch('open-slideover', 'customer-create')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Yeni Müşteri
                    </button>
                    <button @click="$dispatch('open-slideover', 'receivable-create')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Yeni Alacak
                    </button>
                    <button @click="$dispatch('open-slideover', 'task-create')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Yeni Görev
                    </button>
                    <form method="POST" action="{{ route('command-center.run-notifications') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            Hatırlatmaları Şimdi Gönder
                        </button>
                    </form>
                    <button @click="$dispatch('open-slideover', 'settings')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition" title="Ayarlar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </div>

            {{-- KPI Cards (compact) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                <a href="{{ route('dashboard', ['tab' => 'due_today']) }}" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bugün</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($dueToday->total, 2) }} <span class="text-xs font-normal">TL</span></p>
                        </div>
                        <span class="p-1.5 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $dueToday->count }} alacak</p>
                </a>

                <a href="{{ route('dashboard', ['tab' => 'overdue']) }}" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Geciken</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($overdue->total, 2) }} <span class="text-xs font-normal">TL</span></p>
                        </div>
                        <span class="p-1.5 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $overdue->count }} alacak</p>
                </a>

                <a href="{{ route('dashboard', ['tab' => 'upcoming']) }}" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Yaklaşan (7g)</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($upcoming->total, 2) }} <span class="text-xs font-normal">TL</span></p>
                        </div>
                        <span class="p-1.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $upcoming->count }} alacak</p>
                </a>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Son 24s Bildirim</p>
                    <div class="flex items-center gap-4">
                        <div class="text-xs">
                            <span class="text-gray-400">SMS</span>
                            <span class="ml-1 text-emerald-600 font-medium">{{ $smsStats->success_count ?? 0 }}</span>
                            <span class="text-gray-300 dark:text-gray-600">/</span>
                            <span class="text-red-500 font-medium">{{ $smsStats->failed_count ?? 0 }}</span>
                        </div>
                        <div class="text-xs">
                            <span class="text-gray-400">Mail</span>
                            <span class="ml-1 text-emerald-600 font-medium">{{ $mailStats->success_count ?? 0 }}</span>
                            <span class="text-gray-300 dark:text-gray-600">/</span>
                            <span class="text-red-500 font-medium">{{ $mailStats->failed_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content: 2 columns --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Receivables Table (col-span-2) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        {{-- Tabs --}}
                        <div class="border-b border-gray-200 dark:border-gray-700 px-4 pt-3">
                            <div class="flex items-center gap-1 -mb-px">
                                @foreach(['overdue' => 'Geciken', 'due_today' => 'Bugün', 'upcoming' => 'Yaklaşan', 'paid' => 'Ödenmiş'] as $tabKey => $tabLabel)
                                    <a href="{{ route('dashboard', array_merge(request()->only(['q', 'customer_id', 'date_from', 'date_to']), ['tab' => $tabKey])) }}"
                                       class="px-3 py-2 text-sm font-medium border-b-2 transition {{ $tab === $tabKey ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                                        {{ $tabLabel }}
                                        @if($tabKey === 'overdue' && $overdue->count > 0)
                                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">{{ $overdue->count }}</span>
                                        @endif
                                        @if($tabKey === 'due_today' && $dueToday->count > 0)
                                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">{{ $dueToday->count }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-2.5">Müşteri</th>
                                        <th class="px-4 py-2.5">Tutar</th>
                                        <th class="px-4 py-2.5">Vade</th>
                                        @if($tab === 'overdue')
                                            <th class="px-4 py-2.5">Geciken</th>
                                        @endif
                                        <th class="px-4 py-2.5 text-right">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($receivables as $receivable)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $receivable->customer->name ?? '-' }}</span>
                                                    @if($receivable->installmentLabel())
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $receivable->installmentLabel() }}</span>
                                                    @endif
                                                </div>
                                                @if($receivable->description)
                                                    <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $receivable->description }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ number_format($receivable->amount, 2) }} TL</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 text-xs">{{ $receivable->due_date->format('d.m.Y') }}</td>
                                            @if($tab === 'overdue')
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                        {{ (int) now()->diffInDays($receivable->due_date, false) * -1 }} gün
                                                    </span>
                                                </td>
                                            @endif
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($receivable->status !== 'paid')
                                                        <button @click="$dispatch('open-confirm', { name: 'mark-paid', action: '{{ route('command-center.mark-paid', $receivable) }}' })"
                                                                class="p-1.5 rounded text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition" title="Ödendi yap">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        </button>
                                                        <button @click="$dispatch('open-confirm', { name: 'send-notify', action: '{{ route('command-center.notify-receivable', $receivable) }}' })"
                                                                class="p-1.5 rounded text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition" title="SMS+Mail gönder">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('dashboard', ['modal' => 'receivable-edit', 'id' => $receivable->id, 'tab' => $tab]) }}"
                                                       class="p-1.5 rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Düzenle">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $tab === 'overdue' ? 5 : 4 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                                Bu kategoride alacak bulunmuyor.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($receivables->hasPages())
                            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                                {{ $receivables->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right: Tasks + Recent Notifications --}}
                <div class="space-y-6">

                    {{-- Tasks --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-4 pt-3">
                            <div class="flex items-center gap-1 -mb-px">
                                @foreach(['todo' => 'Yapılacak', 'doing' => 'Yapılıyor', 'done' => 'Bitti'] as $tKey => $tLabel)
                                    <a href="{{ route('dashboard', array_merge(request()->only(['tab', 'q', 'customer_id', 'date_from', 'date_to']), ['task_tab' => $tKey])) }}"
                                       class="px-2.5 py-2 text-xs font-medium border-b-2 transition {{ $taskTab === $tKey ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                        {{ $tLabel }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-y-auto">
                            @forelse($tasks as $task)
                                <li class="px-4 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $task->title }}</p>
                                            @if($task->customer)
                                                <p class="text-xs text-gray-400">{{ $task->customer->name }}</p>
                                            @endif
                                        </div>
                                        @if($task->due_date)
                                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ $task->due_date->format('d.m') }}</span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="px-4 py-6 text-center text-sm text-gray-400">Görev yok.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Recent Notifications --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Son Bildirimler</h3>
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-56 overflow-y-auto">
                            @php
                                $notifications = collect()
                                    ->concat($recentSms->map(fn($s) => ['type' => 'sms', 'target' => $s->phone, 'status' => $s->status, 'customer' => $s->customer?->name, 'at' => $s->created_at]))
                                    ->concat($recentMails->map(fn($m) => ['type' => 'mail', 'target' => $m->email, 'status' => $m->status, 'customer' => $m->customer?->name, 'at' => $m->created_at]))
                                    ->sortByDesc('at')
                                    ->take(10);
                            @endphp
                            @forelse($notifications as $notif)
                                <li class="px-4 py-2.5 flex items-center gap-2">
                                    @if($notif['type'] === 'sms')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">SMS</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">Mail</span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $notif['customer'] ?? $notif['target'] }}</p>
                                    </div>
                                    @if(in_array($notif['status'], ['sent', 'success']))
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                    @endif
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $notif['at']->diffForHumans(short: true) }}</span>
                                </li>
                            @empty
                                <li class="px-4 py-6 text-center text-sm text-gray-400">Bildirim yok.</li>
                            @endforelse
                        </ul>
                        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                            <a href="{{ route('sms-logs.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Gönderilen SMS'ler</a>
                            <a href="{{ route('mail-logs.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Gönderilen E-postalar</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- SLIDEOVER: New Customer --}}
    <x-slideover name="customer-create" title="Yeni Müşteri" maxWidth="md">
        <form method="POST" action="{{ route('command-center.store-customer') }}">
            @csrf
            <input type="hidden" name="tab" value="{{ $tab }}">
            @include('customers._form_fields')
            <div class="flex items-center gap-4 mt-6">
                <x-primary-button>Kaydet</x-primary-button>
                <button type="button" @click="$dispatch('close-slideover', 'customer-create')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</button>
            </div>
        </form>
    </x-slideover>

    {{-- SLIDEOVER: New Receivable --}}
    <x-slideover name="receivable-create" title="Yeni Alacak" maxWidth="md">
        <form method="POST" action="{{ route('command-center.store-receivable') }}">
            @csrf
            <input type="hidden" name="tab" value="{{ $tab }}">
            @include('receivables._form_fields', ['receivable' => null, 'customers' => $customers])
            <div class="flex items-center gap-4 mt-6">
                <x-primary-button>Kaydet</x-primary-button>
                <button type="button" @click="$dispatch('close-slideover', 'receivable-create')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</button>
            </div>
        </form>
    </x-slideover>

    {{-- SLIDEOVER: New Task --}}
    <x-slideover name="task-create" title="Yeni Görev" maxWidth="md">
        <form method="POST" action="{{ route('command-center.store-task') }}">
            @csrf
            <input type="hidden" name="tab" value="{{ $tab }}">
            @include('tasks._form_fields', ['task' => null, 'customers' => $customers])
            <div class="flex items-center gap-4 mt-6">
                <x-primary-button>Kaydet</x-primary-button>
                <button type="button" @click="$dispatch('close-slideover', 'task-create')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</button>
            </div>
        </form>
    </x-slideover>

    {{-- SLIDEOVER: Edit Receivable --}}
    @if($editReceivable)
        <x-slideover name="receivable-edit" title="Alacak Düzenle" maxWidth="md">
            <form method="POST" action="{{ route('command-center.update-receivable', $editReceivable) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="{{ $tab }}">
                @include('receivables._form_fields', ['receivable' => $editReceivable, 'customers' => $customers])
                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>Güncelle</x-primary-button>
                    <a href="{{ route('dashboard', ['tab' => $tab]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</a>
                </div>
            </form>
        </x-slideover>
    @endif

    {{-- CONFIRM: Mark Paid --}}
    <x-confirm-modal name="mark-paid"
        title="Ödendi Olarak İşaretle"
        message="Bu alacagi odendi olarak isaretlemek istediginize emin misiniz?"
        confirmText="Ödendi Yap"
        confirmColor="emerald" />

    {{-- CONFIRM: Send Notification --}}
    <x-confirm-modal name="send-notify"
        title="Bildirim Gönder"
        message="Bu alacak icin SMS ve Mail bildirimi göndermek istediginize emin misiniz?"
        confirmText="Gönder"
        confirmColor="indigo" />

    {{-- SLIDEOVER: Settings --}}
    <x-slideover name="settings" title="Bildirim Ayarlari" maxWidth="md">
        <form method="POST" action="{{ route('command-center.update-settings') }}">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <x-input-label for="notification_time" value="Gunluk Bildirim Saati" />
                    <x-text-input id="notification_time" name="notification_time" type="time" class="mt-1 block w-full" :value="old('notification_time', $tenant->notification_time ?? '10:00')" required />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Her gün bu saatte otomatik SMS ve Mail gönderilir.</p>
                    <x-input-error :messages="$errors->get('notification_time')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <x-input-label value="SMS Bildirimi" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">Vade hatirlatmasi SMS gönder</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="sms_enabled" value="0">
                        <input type="checkbox" name="sms_enabled" value="1" class="sr-only peer" {{ ($tenant->sms_enabled ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <x-input-label value="Mail Bildirimi" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">Vade hatirlatmasi e-posta gönder</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="mail_enabled" value="0">
                        <input type="checkbox" name="mail_enabled" value="1" class="sr-only peer" {{ ($tenant->mail_enabled ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-6">
                <x-primary-button>Kaydet</x-primary-button>
                <button type="button" @click="$dispatch('close-slideover', 'settings')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</button>
            </div>
        </form>
    </x-slideover>

    {{-- Auto-open edit slideover --}}
    @if($editReceivable)
        <script>
            document.addEventListener('alpine:init', () => {
                setTimeout(() => window.dispatchEvent(new CustomEvent('open-slideover', { detail: 'receivable-edit' })), 100);
            });
        </script>
    @endif

    {{-- Auto-open slideover on validation error --}}
    @if($errors->any())
        @php
            $modal = request('modal', old('_modal'));
        @endphp
        @if($modal)
            <script>
                document.addEventListener('alpine:init', () => {
                    setTimeout(() => window.dispatchEvent(new CustomEvent('open-slideover', { detail: '{{ $modal }}' })), 100);
                });
            </script>
        @endif
    @endif
</x-app-layout>
