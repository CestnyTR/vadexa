<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Musteriler</h2>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 transition">
                + Yeni Musteri
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search -->
            <div class="mb-6">
                <form method="GET" action="{{ route('customers.index') }}">
                    <div class="flex gap-3">
                        <x-text-input name="q" value="{{ request('q') }}" placeholder="Ad veya telefon ile ara..." class="flex-1" />
                        <x-primary-button>Ara</x-primary-button>
                        @if(request('q'))
                            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Temizle</a>
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
                                <th class="px-6 py-3">Ad</th>
                                <th class="px-6 py-3">Telefon</th>
                                <th class="px-6 py-3">E-posta</th>
                                <th class="px-6 py-3">Notlar</th>
                                <th class="px-6 py-3 text-right">Islemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($customers as $customer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $customer->name }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $customer->phone }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $customer->email ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $customer->notes ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">Duzenle</a>
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Musteri silinsin mi?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">Sil</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Musteri bulunamadi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($customers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
