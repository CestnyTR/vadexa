<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mesaj Şablonları</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Müşterilere gönderilen hatırlatma mesajlarının metnini buradan düzenleyin.</p>
        </div>
    </x-slot>

    @php
        $typeLabels = ['upcoming' => 'Yaklaşan (yarın vadeli)', 'due_today' => 'Bugün vadeli', 'overdue' => 'Gecikmiş'];
        $channels = ['sms' => 'SMS', 'mail' => 'E-posta'];
    @endphp

    <div class="py-8" x-data="{ tab: 'sms' }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 text-sm text-indigo-800 dark:text-indigo-300">
                Mesajlarınızda şu kısayolları kullanabilirsiniz — gönderilirken otomatik doldurulur:
                <span class="font-mono font-semibold">&#123;ad&#125;</span> (müşteri adı),
                <span class="font-mono font-semibold">&#123;tutar&#125;</span> (borç tutarı),
                <span class="font-mono font-semibold">&#123;vade&#125;</span> (vade tarihi).
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
                @foreach ($channels as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}'
                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                            class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Panels -->
            @foreach ($channels as $key => $label)
                <div x-show="tab === '{{ $key }}'" x-cloak class="space-y-5">
                    @foreach (($templates[$key] ?? []) as $template)
                        <form method="POST" action="{{ route('templates.update', $template) }}"
                              class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            @csrf @method('PUT')
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ $typeLabels[$template->type] ?? $template->type }}
                            </label>
                            <textarea name="body" rows="2"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">{{ old('body', $template->body) }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-1" />
                            <div class="mt-3 text-right">
                                <x-primary-button>Kaydet</x-primary-button>
                            </div>
                        </form>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
