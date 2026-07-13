<div class="space-y-6">
    <div>
        <x-input-label for="customer_id" value="Müşteri" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            <option value="">Müşteri seçin...</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $receivable->customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="amount" value="Tutar (TL)" />
        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $receivable->amount ?? '')" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_date" value="Vade Tarihi" />
        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', isset($receivable) ? $receivable->due_date->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Durum" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            @foreach(['upcoming' => 'Yaklaşan', 'due_today' => 'Bugün', 'overdue' => 'Geçmiş', 'paid' => 'Ödendi'] as $key => $label)
                <option value="{{ $key }}" {{ old('status', $receivable->status ?? 'upcoming') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Açıklama" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm">{{ old('description', $receivable->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($receivable) ? 'Güncelle' : 'Kaydet' }}</x-primary-button>
        <a href="{{ route('receivables.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">İptal</a>
    </div>
</div>
