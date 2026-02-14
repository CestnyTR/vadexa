<div class="space-y-6">
    <div>
        <x-input-label for="cc_name" value="Ad Soyad" />
        <x-text-input id="cc_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cc_phone" value="Telefon" />
        <x-text-input id="cc_phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $customer->phone ?? '')" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cc_email" value="E-posta" />
        <x-text-input id="cc_email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cc_notes" value="Notlar" />
        <textarea id="cc_notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm">{{ old('notes', $customer->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>
