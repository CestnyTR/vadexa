<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Plan & Kullanım</h2>
    </x-slot>

    @php $isOwner = Auth::user()->isOwner(); @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Current plan + trial/subscription -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Mevcut plan</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $currentPlan['name'] }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($onTrial)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                Deneme — {{ $tenant->trial_ends_at->diffForHumans() }} bitiyor
                            </span>
                        @endif
                        @if ($subscription && $subscription->isActive())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Abonelik aktif @if($subscription->current_period_ends_at) · {{ $subscription->current_period_ends_at->format('d.m.Y') }} @endif
                            </span>
                            @if ($isOwner)
                                <form method="POST" action="{{ route('billing.cancel') }}"
                                      onsubmit="return confirm('Abonelik iptal edilip Ücretsiz plana geçilsin mi?')">
                                    @csrf
                                    <button class="text-sm text-red-600 dark:text-red-400 hover:underline">İptal et</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Usage bars -->
                <div class="grid sm:grid-cols-2 gap-4 mt-6">
                    @foreach ($usage as $key => $u)
                        @php
                            $label = ['customers' => 'Müşteriler', 'users' => 'Kullanıcılar'][$key] ?? $key;
                            $limit = $u['limit'];
                            $pct = $limit ? min(100, round($u['used'] / max(1, $limit) * 100)) : 0;
                        @endphp
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $u['used'] }}{{ $limit !== null ? ' / '.$limit : ' / ∞' }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full bg-indigo-500" style="width: {{ $limit ? $pct : 8 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Available plans -->
            <div class="grid md:grid-cols-3 gap-4">
                @foreach ($plans as $key => $plan)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6 flex flex-col
                        {{ $tenant->plan === $key ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700' }}">
                        <p class="font-semibold text-lg text-gray-800 dark:text-gray-100">{{ $plan['name'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $plan['price'] == 0 ? 'Ücretsiz' : number_format($plan['price'], 0, ',', '.').' ₺' }}
                            @if ($plan['price'] > 0)<span class="text-sm font-normal text-gray-500">/ay</span>@endif
                        </p>
                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400 flex-1">
                            <li>Müşteri: {{ $plan['limits']['customers'] ?? 'Sınırsız' }}</li>
                            <li>Kullanıcı: {{ $plan['limits']['users'] ?? 'Sınırsız' }}</li>
                            <li>Aylık SMS: {{ $plan['limits']['sms_per_month'] ?? 'Sınırsız' }}</li>
                            <li>Aylık E-posta: {{ $plan['limits']['mail_per_month'] ?? 'Sınırsız' }}</li>
                        </ul>
                        <div class="mt-6">
                            @if ($tenant->plan === $key)
                                <span class="block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400">Aktif plan</span>
                            @elseif ($plan['price'] == 0)
                                @if ($isOwner && $subscription && $subscription->isActive())
                                    <form method="POST" action="{{ route('billing.cancel') }}"
                                          onsubmit="return confirm('Ücretsiz plana geçilsin mi?')">
                                        @csrf
                                        <button class="w-full px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                                            Ücretsiz'e geç
                                        </button>
                                    </form>
                                @else
                                    <span class="block text-center text-xs text-gray-400">Ücretsiz plan</span>
                                @endif
                            @elseif ($isOwner)
                                <form method="POST" action="{{ route('billing.subscribe') }}">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $key }}">
                                    <button class="w-full px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white">
                                        Bu plana geç
                                    </button>
                                </form>
                            @else
                                <span class="block text-center text-xs text-gray-400">Yükseltme için yönetici</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Payment history -->
            @if ($payments->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Ödeme Geçmişi</h3>
                    </div>
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3">Tarih</th>
                                <th class="px-6 py-3">Plan</th>
                                <th class="px-6 py-3">Tutar</th>
                                <th class="px-6 py-3">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($payments as $payment)
                                <tr>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ $plans[$payment->plan]['name'] ?? $payment->plan }}</td>
                                    <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td>
                                    <td class="px-6 py-3">
                                        <x-status-badge :status="$payment->status" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (config('services.iyzico.mock'))
                <p class="text-xs text-gray-400 dark:text-gray-500 text-center">
                    Ödeme sağlayıcı test (mock) modunda — ödemeler simüle ediliyor. Canlıya almak için Iyzico API anahtarları girilmeli.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
