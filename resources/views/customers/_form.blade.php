<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Ad Soyad" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Telefon" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $customer->phone ?? '')" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="E-posta" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="notes" value="Notlar" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm">{{ old('notes', $customer->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($customer) ? 'Guncelle' : 'Kaydet' }}</x-primary-button>
        <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Iptal</a>
    </div>
</div>
