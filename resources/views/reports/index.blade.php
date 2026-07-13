<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Raporlar</h2>
    </x-slot>

    @php
        $agingLabels = [
            'not_due' => 'Vadesi gelmemiş',
            '1_30' => '1–30 gün gecikmiş',
            '31_60' => '31–60 gün gecikmiş',
            '61_90' => '61–90 gün gecikmiş',
            '90_plus' => '90+ gün gecikmiş',
        ];
        $agingColors = [
            'not_due' => 'bg-emerald-500',
            '1_30' => 'bg-amber-400',
            '31_60' => 'bg-orange-500',
            '61_90' => 'bg-red-500',
            '90_plus' => 'bg-red-700',
        ];
        $maxAmount = max(1, collect($aging)->max('amount'));
        $fmt = fn ($n) => number_format($n, 2, ',', '.').' ₺';
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Collection summary -->
            <div class="grid sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Tahsilat Oranı</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">%{{ number_format($collection['rate'], 1, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Toplam Alacak</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $fmt($collection['total_amount']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Tahsil Edilen</p>
                    <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $fmt($collection['paid_amount']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Bekleyen</p>
                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $fmt($collection['outstanding_amount']) }}</p>
                </div>
            </div>

            <!-- Aging report -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">Alacak Yaşlandırma</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Ödenmemiş alacakların vade durumuna göre dağılımı.</p>

                <div class="space-y-4">
                    @foreach ($aging as $key => $bucket)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $agingLabels[$key] }}
                                    <span class="text-gray-400">({{ $bucket['count'] }})</span>
                                </span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $fmt($bucket['amount']) }}</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full {{ $agingColors[$key] }}" style="width: {{ round($bucket['amount'] / $maxAmount * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
