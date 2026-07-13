<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <strong>{{ $invitation->tenant->name }}</strong> ekibine davet edildiniz.
        Hesabınızı oluşturmak için aşağıdaki formu doldurun.
    </div>

    <form method="POST" action="{{ route('invitations.accept.store', $invitation->token) }}">
        @csrf

        <!-- Email (fixed) -->
        <div>
            <x-input-label for="email" :value="__('E-posta')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="email"
                          :value="$invitation->email" disabled />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Ad Soyad')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Parola')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Parola Tekrar')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Kaydol ve Katıl') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
