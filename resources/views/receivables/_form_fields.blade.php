<div class="space-y-6" x-data="{ recurrence: '{{ old('recurrence_type', $receivable->recurrence_type ?? 'once') }}' }">
    <div>
        <x-input-label for="rc_customer_id" value="Musteri" />
        <select id="rc_customer_id" name="customer_id" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            <option value="">Musteri secin...</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $receivable->customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rc_amount" value="Tutar (TL)" />
        <x-text-input id="rc_amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $receivable->amount ?? '')" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rc_due_date" value="Vade Tarihi" />
        <x-text-input id="rc_due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', isset($receivable) && $receivable->due_date ? $receivable->due_date->format('Y-m-d') : '')" required />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="recurrence !== 'once'" x-cloak>Ilk taksitin vade tarihi. Sonrakiler otomatik olusturulur.</p>
        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rc_status" value="Durum" />
        <select id="rc_status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            @foreach(['upcoming' => 'Yaklasan', 'due_today' => 'Bugun', 'overdue' => 'Gecmis', 'paid' => 'Odendi'] as $key => $label)
                <option value="{{ $key }}" {{ old('status', $receivable->status ?? 'upcoming') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rc_description" value="Aciklama" />
        <textarea id="rc_description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm">{{ old('description', $receivable->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    @if(!isset($receivable) || !$receivable?->id)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <x-input-label for="rc_recurrence_type" value="Tekrar / Taksit" />
            <select id="rc_recurrence_type" name="recurrence_type" x-model="recurrence" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                <option value="once">Tek Seferlik</option>
                <option value="weekly">Haftalik</option>
                <option value="biweekly">2 Haftada Bir</option>
                <option value="monthly">Aylik</option>
                <option value="yearly">Yillik</option>
            </select>
            <x-input-error :messages="$errors->get('recurrence_type')" class="mt-2" />
        </div>

        <div x-show="recurrence !== 'once'" x-cloak x-transition>
            <x-input-label for="rc_installment_count" value="Taksit Sayisi" />
            <x-text-input id="rc_installment_count" name="installment_count" type="number" min="2" max="120" class="mt-1 block w-full" :value="old('installment_count', '')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kac taksit olusturulacak (min. 2)</p>
            <x-input-error :messages="$errors->get('installment_count')" class="mt-2" />
        </div>
    @endif
</div>
