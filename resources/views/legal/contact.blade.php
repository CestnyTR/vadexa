<x-public-layout title="İletişim">
    @php $type = config('company.type', 'tacir'); @endphp

    <p>Aşağıda firmamıza ait yasal künye ve iletişim bilgileri yer almaktadır.</p>

    <div class="not-prose mt-4 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 overflow-hidden bg-white dark:bg-gray-800">
        @php
            $rows = $type === 'esnaf'
                ? [
                    'Ad Soyad' => config('company.legal_name'),
                    'Vergi Kimlik Numarası' => config('company.tax_number'),
                    'Vergi Dairesi' => config('company.tax_office'),
                    'Merkez Adresi' => config('company.address'),
                ]
                : [
                    'Ticaret Unvanı' => config('company.legal_name'),
                    'MERSİS Numarası' => config('company.mersis'),
                    'Merkez Adresi' => config('company.address'),
                ];

            $rows += [
                'İşletme Adı' => config('company.brand'),
                'Tescilli Marka' => config('company.trademark'),
                'KEP Adresi' => config('company.kep'),
                'E-posta' => config('company.email'),
                'Telefon' => config('company.phone'),
                'Meslek Odası' => config('company.chamber'),
            ];
        @endphp

        @foreach ($rows as $label => $value)
            <div class="flex flex-col sm:flex-row sm:items-center px-5 py-3 text-sm">
                <span class="w-full sm:w-56 shrink-0 text-gray-500 dark:text-gray-400">{{ $label }}</span>
                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $value ?: '—' }}</span>
            </div>
        @endforeach
    </div>

    <p class="mt-6">
        Mensubu olduğumuz meslek odası <strong>{{ config('company.chamber') }}</strong> olup, meslekle ilgili
        davranış kurallarına
        @if (config('company.code_of_conduct_url'))
            <a href="{{ config('company.code_of_conduct_url') }}" class="text-indigo-600 dark:text-indigo-400" target="_blank" rel="noopener">bu bağlantıdan</a>
        @else
            ilgili meslek odasının resmi internet sitesinden
        @endif
        elektronik olarak ulaşabilirsiniz.
    </p>

    <div class="not-prose mt-6">
        <x-payment-badges />
    </div>
</x-public-layout>
