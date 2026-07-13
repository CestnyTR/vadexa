<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ekip</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Invite form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Yeni üye davet et</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $userUsage }}{{ $userLimit !== null ? ' / '.$userLimit : '' }} kullanıcı
                    </span>
                </div>
                <form method="POST" action="{{ route('invitations.store') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <div class="flex-1">
                        <x-text-input name="email" type="email" placeholder="e-posta adresi" value="{{ old('email') }}" class="w-full" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <select name="role" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="member">Üye</option>
                        <option value="owner">Yönetici</option>
                    </select>
                    <x-primary-button>Davet Et</x-primary-button>
                </form>
            </div>

            <!-- Members -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Üyeler</h3>
                </div>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3">Ad</th>
                            <th class="px-6 py-3">E-posta</th>
                            <th class="px-6 py-3">Rol</th>
                            <th class="px-6 py-3 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($members as $member)
                            <tr>
                                <td class="px-6 py-3 text-gray-800 dark:text-gray-200">
                                    {{ $member->name }}
                                    @if ($member->id === Auth::id())
                                        <span class="text-xs text-gray-400">(siz)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $member->email }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $member->isOwner() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $member->isOwner() ? 'Yönetici' : 'Üye' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('team.members.update', $member) }}">
                                            @csrf @method('PATCH')
                                            <select name="role" onchange="this.form.submit()"
                                                    class="text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 py-1">
                                                <option value="member" {{ $member->isOwner() ? '' : 'selected' }}>Üye</option>
                                                <option value="owner" {{ $member->isOwner() ? 'selected' : '' }}>Yönetici</option>
                                            </select>
                                        </form>
                                        @if ($member->id !== Auth::id())
                                            <form method="POST" action="{{ route('team.members.destroy', $member) }}"
                                                  onsubmit="return confirm('{{ $member->name }} ekipten çıkarılsın mı?')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 dark:text-red-400 text-sm hover:underline">Çıkar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pending invitations -->
            @if ($invitations->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Bekleyen Davetler</h3>
                    </div>
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($invitations as $invitation)
                                <tr>
                                    <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ $invitation->email }}</td>
                                    <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                        Son: {{ $invitation->expires_at->format('d.m.Y') }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <input readonly value="{{ route('invitations.accept', $invitation->token) }}"
                                               class="w-full text-xs bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded px-2 py-1 text-gray-600 dark:text-gray-300" onclick="this.select()">
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST" action="{{ route('invitations.destroy', $invitation) }}"
                                              onsubmit="return confirm('Davet iptal edilsin mi?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400 text-sm hover:underline">İptal</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
