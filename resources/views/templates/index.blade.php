<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mesaj Şablonları</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 text-sm text-indigo-800 dark:text-indigo-300">
                Kullanabileceğiniz değişkenler:
                <code class="font-mono">&#123;ad&#125;</code>,
                <code class="font-mono">&#123;tutar&#125;</code>,
                <code class="font-mono">&#123;vade&#125;</code>
            </div>

            @php
                $typeLabels = ['upcoming' => 'Yaklaşan (yarın)', 'due_today' => 'Bugün vadeli', 'overdue' => 'Gecikmiş'];
                $channelLabels = ['sms' => 'SMS', 'mail' => 'E-posta'];
            @endphp

            @foreach ($templates as $channel => $group)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ $channelLabels[$channel] ?? $channel }} Şablonları</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        @foreach ($group as $template)
                            <form method="POST" action="{{ route('templates.update', $template) }}">
                                @csrf @method('PUT')
                                <x-input-label :value="$typeLabels[$template->type] ?? $template->type" />
                                <div class="flex gap-3 mt-1">
                                    <textarea name="body" rows="2"
                                              class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">{{ old('body', $template->body) }}</textarea>
                                    <x-primary-button class="self-start">Kaydet</x-primary-button>
                                </div>
                                <x-input-error :messages="$errors->get('body')" class="mt-1" />
                            </form>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
