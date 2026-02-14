<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mail Log</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('mail-logs.index') }}">
                    <div class="flex flex-wrap gap-3">
                        <x-text-input name="q" value="{{ request('q') }}" placeholder="E-posta ile ara..." class="flex-1 min-w-[200px]" />
                        <select name="status" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            <option value="">Tum Durumlar</option>
                            @foreach(['success' => 'Basarili', 'failed' => 'Basarisiz'] as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            <option value="">Tum Tipler</option>
                            @foreach(['upcoming' => 'Yaklasan', 'due_today' => 'Bugun', 'overdue' => 'Gecmis'] as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-text-input name="date_from" type="date" value="{{ request('date_from') }}" class="text-sm" />
                        <x-text-input name="date_to" type="date" value="{{ request('date_to') }}" class="text-sm" />
                        <x-primary-button>Filtrele</x-primary-button>
                        @if(request()->hasAny(['q', 'status', 'type', 'date_from', 'date_to']))
                            <a href="{{ route('mail-logs.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Temizle</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3">Tarih</th>
                                <th class="px-6 py-3">E-posta</th>
                                <th class="px-6 py-3">Musteri</th>
                                <th class="px-6 py-3">Tip</th>
                                <th class="px-6 py-3">Durum</th>
                                <th class="px-6 py-3">Konu</th>
                                <th class="px-6 py-3">Hata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $log->email }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $log->customer->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $typeColors = [
                                                'upcoming' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                                                'due_today' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300',
                                                'overdue' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$log->type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $log->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'success' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300',
                                                'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $log->subject }}</td>
                                    <td class="px-6 py-4 text-red-500 dark:text-red-400 max-w-xs truncate">{{ $log->error ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Mail log bulunamadi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
