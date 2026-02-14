<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gorevler</h2>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 transition">
                + Yeni Gorev
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Quick Filter Chips -->
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    Tumu
                </a>
                @foreach(['todo' => 'Yapilacak', 'doing' => 'Yapiliyor', 'done' => 'Tamamlandi'] as $key => $label)
                    <a href="{{ route('tasks.index', array_merge(request()->except('page'), ['status' => $key])) }}" class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ request('status') === $key ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('tasks.index') }}">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="flex flex-wrap gap-3">
                        <x-text-input name="q" value="{{ request('q') }}" placeholder="Baslik ile ara..." class="flex-1 min-w-[200px]" />
                        <x-text-input name="due_date_from" type="date" value="{{ request('due_date_from') }}" class="text-sm" />
                        <x-text-input name="due_date_to" type="date" value="{{ request('due_date_to') }}" class="text-sm" />
                        <x-primary-button>Filtrele</x-primary-button>
                        @if(request()->hasAny(['q', 'due_date_from', 'due_date_to', 'status']))
                            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Temizle</a>
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
                                <th class="px-6 py-3">Baslik</th>
                                <th class="px-6 py-3">Durum</th>
                                <th class="px-6 py-3">Musteri</th>
                                <th class="px-6 py-3">Vade Tarihi</th>
                                <th class="px-6 py-3 text-right">Islemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($tasks as $task)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $task->title }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'todo' => 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200',
                                                'doing' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300',
                                                'done' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300',
                                            ];
                                            $statusLabels = [
                                                'todo' => 'Yapilacak',
                                                'doing' => 'Yapiliyor',
                                                'done' => 'Tamamlandi',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$task->status] ?? $task->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $task->customer->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $task->due_date?->format('d.m.Y') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">Duzenle</a>
                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Gorev silinsin mi?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">Sil</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Gorev bulunamadi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tasks->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
