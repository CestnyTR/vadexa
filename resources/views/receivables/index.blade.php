<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Alacaklar</h2>
            <a href="{{ route('receivables.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 transition">
                + Yeni Alacak
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Quick Filter Chips -->
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('receivables.index') }}" class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    Tümü
                </a>
                @foreach(['upcoming' => 'Yaklaşan', 'due_today' => 'Bugün', 'overdue' => 'Geçmiş', 'paid' => 'Ödendi'] as $key => $label)
                    <a href="{{ route('receivables.index', array_merge(request()->except('page'), ['status' => $key])) }}" class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ request('status') === $key ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('receivables.index') }}">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="flex flex-wrap gap-3">
                        <x-text-input name="q" value="{{ request('q') }}" placeholder="Müşteri adı/telefon..." class="flex-1 min-w-[200px]" />
                        <select name="customer_id" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            <option value="">Tüm Müşteriler</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <x-text-input name="due_date_from" type="date" value="{{ request('due_date_from') }}" class="text-sm" />
                        <x-text-input name="due_date_to" type="date" value="{{ request('due_date_to') }}" class="text-sm" />
                        <x-primary-button>Filtrele</x-primary-button>
                        @if(request()->hasAny(['q', 'customer_id', 'due_date_from', 'due_date_to', 'status']))
                            <a href="{{ route('receivables.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Temizle</a>
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
                                <th class="px-6 py-3">Müşteri</th>
                                <th class="px-6 py-3">Tutar</th>
                                <th class="px-6 py-3">Vade Tarihi</th>
                                <th class="px-6 py-3">Durum</th>
                                <th class="px-6 py-3">Açıklama</th>
                                <th class="px-6 py-3 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($receivables as $receivable)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $receivable->customer->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium">{{ number_format($receivable->amount, 2) }} TL</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $receivable->due_date->format('d.m.Y') }}</td>
                                    <td class="px-6 py-4">
                                        <x-status-badge :status="$receivable->status" />
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $receivable->description ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('receivables.edit', $receivable) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">Düzenle</a>
                                            <form method="POST" action="{{ route('receivables.destroy', $receivable) }}" onsubmit="return confirm('Alacak silinsin mi?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">Sil</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Alacak bulunamadı.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($receivables->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $receivables->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
