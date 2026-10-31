<div>
    @if($isOpen)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            x-data
            x-on:keydown.escape.window="$wire.closeModal()"
        >
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                wire:click="closeModal"
            ></div>

            <!-- Modal Panel -->
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800" id="modal-title">Pengaturan Akun & Profil</h3>
                                <p class="text-xs text-slate-500">Kelola identitas, keamanan sandi, dan perangkat aktif Anda</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                        >
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="flex border-b border-slate-200 px-6 space-x-6 text-sm font-medium text-slate-600 bg-white">
                        <button 
                            type="button"
                            wire:click="$set('activeTab', 'profile')"
                            class="py-3 border-b-2 transition flex items-center space-x-2 {{ $activeTab === 'profile' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Identitas Diri</span>
                        </button>

                        <button 
                            type="button"
                            wire:click="$set('activeTab', 'password')"
                            class="py-3 border-b-2 transition flex items-center space-x-2 {{ $activeTab === 'password' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Ganti Password</span>
                        </button>

                        <button 
                            type="button"
                            wire:click="$set('activeTab', 'sessions')"
                            class="py-3 border-b-2 transition flex items-center space-x-2 {{ $activeTab === 'sessions' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Sesi & Perangkat</span>
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        @if($activeTab === 'profile')
                            @php
                                $identityVal = $user?->identity_type instanceof \App\Enums\IdentityType 
                                    ? $user->identity_type->value 
                                    : (string) ($user?->identity_type ?? 'pegawai');
                                $isMitra = strtolower($identityVal) === 'mitra';
                                $identityLabel = $user?->identity_type instanceof \App\Enums\IdentityType 
                                    ? $user->identity_type->label() 
                                    : strtoupper($identityVal);
                            @endphp
                            <div class="space-y-5">
                                <div class="flex items-center space-x-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-full ring-2 ring-blue-500/20 bg-slate-200">
                                        @if($user?->avatar_url)
                                            <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center font-bold text-slate-500 text-xl">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-slate-900">{{ $user?->name }}</h4>
                                        <p class="text-xs text-slate-500">{{ $user?->email }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $isMitra ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $identityLabel }}
                                            </span>
                                            @if(! $isMitra && $user?->nip)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-200 text-slate-700">
                                                    NIP: {{ $user->nip }}
                                                </span>
                                            @endif
                                            @if($isMitra && $user?->sobat_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    SOBAT: {{ $user->sobat_id }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                    @if(! $isMitra)
                                        {{-- KHUSUS PEGAWAI: NIP Baru, NIP Lama, Jabatan, Satker --}}
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">NIP Baru (18 Digit)</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->nip_baru ?: '-' }}</span>
                                        </div>
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">NIP Lama</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->nip ?: '-' }}</span>
                                        </div>
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">Jabatan</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->jabatan ?: '-' }}</span>
                                        </div>
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">Unit Kerja / Satker</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->unit_kerja ?: ($user?->kd_satker ?: 'BPS Kab. Demak') }}</span>
                                        </div>
                                    @else
                                        {{-- KHUSUS MITRA: ID SOBAT, Periode Mitra, Siklus Kontrak --}}
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">ID SOBAT BPS</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block font-mono font-bold text-emerald-700">{{ $user?->sobat_id ?: '-' }}</span>
                                        </div>
                                        <div class="p-3 rounded-lg border border-slate-100 bg-white">
                                            <span class="block text-slate-400 font-medium">Tahun / Periode Mitra</span>
                                            <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->period ?: date('Y') }}</span>
                                        </div>
                                        @if($user?->contract_start || $user?->contract_end)
                                            <div class="p-3 rounded-lg border border-slate-100 bg-white sm:col-span-2">
                                                <span class="block text-slate-400 font-medium">Masa Berlaku Kontrak</span>
                                                <span class="text-slate-800 font-semibold mt-0.5 block">
                                                    {{ $user?->contract_start ? \Carbon\Carbon::parse($user->contract_start)->format('d M Y') : '-' }} s/d 
                                                    {{ $user?->contract_end ? \Carbon\Carbon::parse($user->contract_end)->format('d M Y') : '-' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="p-3 rounded-lg border border-slate-100 bg-white {{ $isMitra ? 'sm:col-span-2' : '' }}">
                                        <span class="block text-slate-400 font-medium">Nomor WhatsApp / Kontak</span>
                                        <span class="text-slate-800 font-semibold mt-0.5 block">{{ $user?->phone ?: '-' }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-400 italic">
                                    *Untuk memperbarui data profil kepegawaian atau mitra, silakan menghubungi administrator SIPETRA.
                                </p>
                            </div>

                        @elseif($activeTab === 'password')
                            <form wire:submit="updatePassword" class="space-y-4">
                                @if($passwordSuccessMessage)
                                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center space-x-2">
                                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $passwordSuccessMessage }}</span>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Saat Ini</label>
                                    <input 
                                        type="password" 
                                        wire:model="current_password" 
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                        placeholder="Masukkan password Anda sekarang"
                                    >
                                    @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru (Minimal 8 Karakter)</label>
                                    <input 
                                        type="password" 
                                        wire:model="new_password" 
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                        placeholder="Ketik password baru yang kuat"
                                    >
                                    @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                                    <input 
                                        type="password" 
                                        wire:model="new_password_confirmation" 
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                        placeholder="Ulangi password baru"
                                    >
                                    @error('new_password_confirmation') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-2 flex justify-end space-x-3">
                                    <button 
                                        type="button" 
                                        wire:click="closeModal" 
                                        class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition"
                                    >
                                        Batal
                                    </button>
                                    <button 
                                        type="submit" 
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition flex items-center space-x-1"
                                    >
                                        <span wire:loading.remove wire:target="updatePassword">Simpan Password</span>
                                        <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                                    </button>
                                </div>
                            </form>

                        @elseif($activeTab === 'sessions')
                            <div class="space-y-4">
                                @if($sessionSuccessMessage)
                                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center space-x-2">
                                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $sessionSuccessMessage }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-slate-500">
                                        Daftar browser dan perangkat yang sedang masuk menggunakan akun Anda:
                                    </p>
                                    @if(count($sessions) > 1)
                                        <button 
                                            type="button" 
                                            wire:click="revokeOtherSessions"
                                            wire:confirm="Yakin ingin mengeluarkan semua sesi di perangkat lain?"
                                            class="px-2.5 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition"
                                        >
                                            Putus Sesi Lain
                                        </button>
                                    @endif
                                </div>

                                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto rounded-xl border border-slate-100">
                                    @forelse($sessions as $session)
                                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/50 transition">
                                            <div class="flex items-center space-x-3">
                                                <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                                                    @if(str_contains(strtolower($session['device']), 'ios') || str_contains(strtolower($session['device']), 'android'))
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs font-bold text-slate-800">{{ $session['device'] }} — {{ $session['browser'] }}</span>
                                                        @if($session['is_current'])
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                                Sesi Saat Ini
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                                        IP: {{ $session['ip_address'] }} • Aktif: {{ $session['last_active'] }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                @if(! $session['is_current'])
                                                    <button 
                                                        type="button" 
                                                        wire:click="revokeSession('{{ $session['id'] }}')" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                        title="Akhiri sesi perangkat ini"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-xs text-slate-400">
                                            Tidak ada riwayat sesi ditemukan.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
