<div class="space-y-6">
    <div>
        <x-input-label for="title" value="Baslik" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $task->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Aciklama" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm">{{ old('description', $task->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Durum" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            @foreach(['todo' => 'Yapilacak', 'doing' => 'Yapiliyor', 'done' => 'Tamamlandi'] as $key => $label)
                <option value="{{ $key }}" {{ old('status', $task->status ?? 'todo') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_date" value="Vade Tarihi" />
        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', isset($task) && $task->due_date ? $task->due_date->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="customer_id" value="Musteri (opsiyonel)" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
            <option value="">Musteri yok</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $task->customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($task) ? 'Guncelle' : 'Kaydet' }}</x-primary-button>
        <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Iptal</a>
    </div>
</div>
