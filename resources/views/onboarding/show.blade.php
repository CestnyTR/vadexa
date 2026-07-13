<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Hoş geldiniz, {{ $tenant->name }} 👋</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Vadexa'ya hoş geldiniz! Hesabınız <strong>{{ config('plans.trial_days') }} günlük ücretsiz deneme</strong> ile başladı.
                    Başlamadan önce bildirim tercihlerinizi ayarlayalım.
                </p>

                <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="notification_time" value="Günlük bildirim saati" />
                        <x-text-input id="notification_time" name="notification_time" type="time"
                                      value="{{ old('notification_time', $tenant->notification_time) }}"
                                      class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('notification_time')" class="mt-2" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Vadesi gelen alacaklar için hatırlatmalar bu saatte gönderilir.</p>
                    </div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="sms_enabled" value="1" {{ old('sms_enabled', $tenant->sms_enabled) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">SMS bildirimleri gönder</span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="mail_enabled" value="1" {{ old('mail_enabled', $tenant->mail_enabled) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">E-posta bildirimleri gönder</span>
                    </label>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Şimdilik geç</a>
                        <x-primary-button>Kaydet ve başla</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
