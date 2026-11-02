<div>
    @if($isOpen)
        <div 
            class="modal-portal-wrapper" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            x-data
            x-on:keydown.escape.window="$wire.closeModal()"
        >
            <style>
                .modal-portal-wrapper {
                    position: fixed;
                    inset: 0;
                    z-index: 99999;
                    overflow-y: auto;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 1rem;
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                }
                .modal-portal-backdrop {
                    position: fixed;
                    inset: 0;
                    background: rgba(15, 23, 42, 0.65);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                    transition: opacity 0.2s ease;
                }
                .modal-portal-card {
                    position: relative;
                    width: 100%;
                    max-width: 660px;
                    background: #ffffff;
                    border-radius: 20px;
                    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
                    border: 1px solid #e2e8f0;
                    overflow: hidden;
                    z-index: 100000;
                    text-align: left;
                    margin: 1.5rem auto;
                    animation: modalFadeIn 0.2s ease-out;
                }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: scale(0.96) translateY(8px); }
                    to { opacity: 1; transform: scale(1) translateY(0); }
                }
                .modal-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 1.125rem 1.5rem;
                    background: #f8fafc;
                    border-bottom: 1px solid #e2e8f0;
                }
                .modal-tabs {
                    display: flex;
                    border-bottom: 1px solid #e2e8f0;
                    background: #ffffff;
                    padding: 0 1.5rem;
                    gap: 1.5rem;
                }
                .modal-tab-btn {
                    padding: 0.875rem 0.25rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    background: transparent;
                    border: none;
                    border-bottom: 2px solid transparent;
                    cursor: pointer;
                    transition: all 0.15s ease;
                    color: #64748b;
                }
                .modal-tab-btn:hover {
                    color: #0f172a;
                }
                .modal-tab-btn.active {
                    color: #0284c7;
                    border-bottom-color: #0284c7;
                }
                .modal-body {
                    padding: 1.5rem;
                }
                .modal-profile-card {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.125rem;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 16px;
                }
                .modal-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 0.875rem;
                    margin-top: 1rem;
                }
                @media (max-width: 640px) {
                    .modal-grid {
                        grid-template-columns: 1fr;
                    }
                    .modal-tabs {
                        gap: 0.75rem;
                        padding: 0 1rem;
                        overflow-x: auto;
                    }
                }
                .modal-field-box {
                    padding: 0.75rem 1rem;
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                }
                .modal-field-label {
                    display: block;
                    font-size: 0.7rem;
                    font-weight: 600;
                    color: #94a3b8;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    margin-bottom: 0.25rem;
                }
                .modal-field-val {
                    display: block;
                    font-size: 0.875rem;
                    font-weight: 700;
                    color: #0f172a;
                    word-break: break-word;
                }
                .modal-input {
                    width: 100%;
                    padding: 0.65rem 0.875rem;
                    font-size: 0.875rem;
                    border: 1.5px solid #cbd5e1;
                    border-radius: 10px;
                    background: #ffffff;
                    color: #0f172a;
                    outline: none;
                    transition: border-color 0.15s, box-shadow 0.15s;
                }
                .modal-input:focus {
                    border-color: #0284c7;
                    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
                }
                .modal-btn-primary {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.35rem;
                    padding: 0.65rem 1.25rem;
                    font-size: 0.8125rem;
                    font-weight: 700;
                    color: #ffffff;
                    background: #0284c7;
                    border: none;
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.15s;
                }
                .modal-btn-primary:hover {
                    background: #0369a1;
                    transform: translateY(-1px);
                }
                .modal-btn-secondary {
                    display: inline-flex;
                    align-items: center;
                    padding: 0.65rem 1rem;
                    font-size: 0.8125rem;
                    font-weight: 600;
                    color: #475569;
                    background: #f1f5f9;
                    border: 1px solid #cbd5e1;
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.15s;
                }
                .modal-btn-secondary:hover {
                    background: #e2e8f0;
                    color: #0f172a;
                }
            </style>

            <!-- Backdrop -->
            <div 
                class="modal-portal-backdrop"
                wire:click="closeModal"
            ></div>

            <!-- Modal Panel -->
            <div class="modal-portal-card">
                
                <!-- Header -->
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="display: flex; height: 40px; width: 40px; align-items: center; justify-content: center; border-radius: 10px; background: #e0f2fe; color: #0284c7;">
                            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;" id="modal-title">Pengaturan Akun & Profil</h3>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0;">Identitas terpusat SIPETRA, keamanan sandi, dan perangkat aktif</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeModal" 
                        style="background: transparent; border: none; padding: 0.35rem; border-radius: 8px; color: #94a3b8; cursor: pointer;"
                        onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'"
                        onmouseout="this.style.background='transparent'; this.style.color='#94a3b8'"
                    >
                        <span class="sr-only">Tutup</span>
                        <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Tabs -->
                <div class="modal-tabs">
                    <button 
                        type="button"
                        wire:click="$set('activeTab', 'profile')"
                        class="modal-tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}"
                    >
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Identitas Diri</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('activeTab', 'password')"
                        class="modal-tab-btn {{ $activeTab === 'password' ? 'active' : '' }}"
                    >
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Ganti Password</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="$set('activeTab', 'sessions')"
                        class="modal-tab-btn {{ $activeTab === 'sessions' ? 'active' : '' }}"
                    >
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Sesi & Perangkat</span>
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="modal-body">
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
                        <div>
                            <div class="modal-profile-card">
                                <div style="height: 60px; width: 60px; flex-shrink: 0; overflow: hidden; border-radius: 50%; border: 2px solid #0284c7; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                    @if($user?->avatar_url)
                                        <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}" style="height: 100%; width: 100%; object-fit: cover;">
                                    @else
                                        <span style="font-weight: 800; color: #475569; font-size: 1.25rem;">
                                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;">{{ $user?->name }}</h4>
                                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0.15rem 0 0;">{{ $user?->email }}</p>
                                    <div style="margin-top: 0.45rem; display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                        <span style="display: inline-flex; align-items: center; padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; background: {{ $isMitra ? '#dcfce7' : '#e0f2fe' }}; color: {{ $isMitra ? '#166534' : '#075985' }};">
                                            {{ $identityLabel }}
                                        </span>
                                        @if(! $isMitra && $user?->nip)
                                            <span style="display: inline-flex; align-items: center; padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.7rem; font-weight: 600; background: #f1f5f9; color: #475569;">
                                                NIP: {{ $user->nip }}
                                            </span>
                                        @endif
                                        @if($isMitra && $user?->sobat_id)
                                            <span style="display: inline-flex; align-items: center; padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; background: #dcfce7; color: #166534;">
                                                SOBAT: {{ $user->sobat_id }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal-grid">
                                @if(! $isMitra)
                                    {{-- KHUSUS PEGAWAI: NIP Baru, NIP Lama, Jabatan, Satker --}}
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">NIP Baru (18 Digit)</span>
                                        <span class="modal-field-val">{{ $user?->nip_baru ?: '-' }}</span>
                                    </div>
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">NIP Lama</span>
                                        <span class="modal-field-val">{{ $user?->nip ?: '-' }}</span>
                                    </div>
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">Jabatan</span>
                                        <span class="modal-field-val">{{ $user?->jabatan ?: '-' }}</span>
                                    </div>
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">Unit Kerja / Satker</span>
                                        <span class="modal-field-val">{{ $user?->unit_kerja ?: ($user?->kd_satker ?: 'BPS Kab. Demak') }}</span>
                                    </div>
                                @else
                                    {{-- KHUSUS MITRA: ID SOBAT, Periode Mitra, Siklus Kontrak --}}
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">ID SOBAT BPS</span>
                                        <span class="modal-field-val" style="font-family: monospace; font-size: 1rem; color: #059669;">{{ $user?->sobat_id ?: '-' }}</span>
                                    </div>
                                    <div class="modal-field-box">
                                        <span class="modal-field-label">Tahun / Periode Mitra</span>
                                        <span class="modal-field-val">{{ $user?->period ?: date('Y') }}</span>
                                    </div>
                                    @if($user?->contract_start || $user?->contract_end)
                                        <div class="modal-field-box" style="grid-column: 1 / -1;">
                                            <span class="modal-field-label">Masa Berlaku Kontrak</span>
                                            <span class="modal-field-val">
                                                {{ $user?->contract_start ? \Carbon\Carbon::parse($user->contract_start)->format('d M Y') : '-' }} s/d 
                                                {{ $user?->contract_end ? \Carbon\Carbon::parse($user->contract_end)->format('d M Y') : '-' }}
                                            </span>
                                        </div>
                                    @endif
                                @endif

                                <div class="modal-field-box {{ $isMitra ? 'modal-col-full' : '' }}" style="{{ $isMitra ? 'grid-column: 1 / -1;' : '' }}">
                                    <span class="modal-field-label">Nomor WhatsApp / Kontak</span>
                                    <span class="modal-field-val">{{ $user?->phone ?: '-' }}</span>
                                </div>
                            </div>

                            <p style="margin-top: 1rem; font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                *Untuk memperbarui data profil kepegawaian atau mitra, silakan menghubungi administrator SIPETRA BPS Kabupaten Demak.
                            </p>
                        </div>

                    @elseif($activeTab === 'password')
                        <form wire:submit="updatePassword" style="display: flex; flex-direction: column; gap: 1rem;">
                            @if($passwordSuccessMessage)
                                <div style="padding: 0.75rem 1rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <svg style="width: 18px; height: 18px; color: #059669; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $passwordSuccessMessage }}</span>
                                </div>
                            @endif

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Password Saat Ini</label>
                                <input 
                                    type="password" 
                                    wire:model="current_password" 
                                    class="modal-input"
                                    placeholder="Masukkan password Anda sekarang"
                                >
                                @error('current_password') <span style="font-size: 0.75rem; color: #e11d48; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Password Baru (Minimal 8 Karakter)</label>
                                <input 
                                    type="password" 
                                    wire:model="new_password" 
                                    class="modal-input"
                                    placeholder="Ketik password baru yang kuat"
                                >
                                @error('new_password') <span style="font-size: 0.75rem; color: #e11d48; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Konfirmasi Password Baru</label>
                                <input 
                                    type="password" 
                                    wire:model="new_password_confirmation" 
                                    class="modal-input"
                                    placeholder="Ulangi password baru"
                                >
                                @error('new_password_confirmation') <span style="font-size: 0.75rem; color: #e11d48; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div style="padding-top: 0.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                                <button 
                                    type="button" 
                                    wire:click="closeModal" 
                                    class="modal-btn-secondary"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    wire:loading.attr="disabled"
                                    class="modal-btn-primary"
                                >
                                    <span wire:loading.remove wire:target="updatePassword">Simpan Password</span>
                                    <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                                </button>
                            </div>
                        </form>

                    @elseif($activeTab === 'sessions')
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @if($sessionSuccessMessage)
                                <div style="padding: 0.75rem 1rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <svg style="width: 18px; height: 18px; color: #059669; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $sessionSuccessMessage }}</span>
                                </div>
                            @endif

                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">
                                    Daftar browser dan perangkat yang sedang masuk menggunakan akun Anda:
                                </p>
                                @if(count($sessions) > 1)
                                    <button 
                                        type="button" 
                                        wire:click="revokeOtherSessions"
                                        wire:confirm="Yakin ingin mengeluarkan semua sesi di perangkat lain?"
                                        style="padding: 0.35rem 0.65rem; font-size: 0.75rem; font-weight: 700; color: #e11d48; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; cursor: pointer;"
                                    >
                                        Putus Sesi Lain
                                    </button>
                                @endif
                            </div>

                            <div style="border: 1px solid #e2e8f0; border-radius: 14px; max-height: 280px; overflow-y: auto; background: #ffffff;">
                                @forelse($sessions as $session)
                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.875rem 1rem; border-bottom: 1px solid #f1f5f9;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="height: 38px; width: 38px; border-radius: 10px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                @if(str_contains(strtolower($session['device']), 'ios') || str_contains(strtolower($session['device']), 'android'))
                                                    <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                @else
                                                    <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">{{ $session['device'] }} — {{ $session['browser'] }}</span>
                                                    @if($session['is_current'])
                                                        <span style="padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700; background: #dcfce7; color: #166534;">
                                                            Sesi Saat Ini
                                                        </span>
                                                    @endif
                                                </div>
                                                <div style="font-size: 0.725rem; color: #94a3b8; margin-top: 0.15rem;">
                                                    IP: {{ $session['ip_address'] }} &bull; Aktif: {{ $session['last_active'] }}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            @if(! $session['is_current'])
                                                <button 
                                                    type="button" 
                                                    wire:click="revokeSession('{{ $session['id'] }}')" 
                                                    style="background: transparent; border: none; padding: 0.35rem; color: #94a3b8; cursor: pointer; border-radius: 6px;"
                                                    title="Akhiri sesi perangkat ini"
                                                    onmouseover="this.style.color='#e11d48'; this.style.background='#fff1f2'"
                                                    onmouseout="this.style.color='#94a3b8'; this.style.background='transparent'"
                                                >
                                                    <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding: 2rem; text-align: center; font-size: 0.8125rem; color: #94a3b8;">
                                        Tidak ada riwayat sesi ditemukan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
