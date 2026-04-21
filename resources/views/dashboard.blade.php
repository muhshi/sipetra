<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profil SSO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-800 antialiased font-sans">

    <!-- Top Navbar -->
    <nav class="bg-white shadow-sm absolute w-full top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="{{ asset('logoBpsDemakHitam.png') }}"
                        alt="Logo BPS Demak"
                        class="h-16 w-auto object-contain dark:hidden">

                    <img src="{{ asset('logoBpsDemakOren.png') }}"
                        alt="Logo BPS Demak"
                        class="hidden h-16 w-auto object-contain dark:block">
                </div>
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-500 mr-4">{{ Auth::user()?->name }}</span>
                    <!-- Simple absolute logout route using form -->
                    <form method="POST" action="{{ route('logout') ?? '/logout' }}" id="logout-form">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800 transition">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center pt-16">
        <div class="max-w-3xl w-full mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-8 border-b border-gray-100 flex items-center bg-gradient-to-r from-blue-50 to-white">
                    @if(Auth::user()?->avatar_url)
                        <img src="{{ Storage::url(Auth::user()->avatar_url) }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover shadow-sm">
                    @else
                        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold shadow-sm">
                            {{ substr(Auth::user()?->name ?? 'U', 0, 1) }}
                        </div>
                    @endif
                    <div class="ml-5">
                        <h2 class="text-2xl font-bold text-gray-900">{{ Auth::user()?->name }}</h2>
                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ (Auth::user()?->identity_type?->value ?? '') === 'Pegawai' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ Auth::user()?->identity_type?->value ?? 'User' }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-6 p-0">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Alamat Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()?->email ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Nomor Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()?->phone ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">{{ (Auth::user()?->identity_type?->value ?? '') === 'Mitra' ? 'SOBAT ID' : 'NIP / NIP Baru' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ (Auth::user()?->identity_type?->value ?? '') === 'Mitra' ? (Auth::user()?->sobat_id ?? '-') : (Auth::user()?->nip_baru ?? Auth::user()?->nip ?? '-') }}
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Organisasi / Satuan Kerja</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ Auth::user()?->unit_kerja ?? 'BPS' }}
                                <span class="block text-xs text-gray-500 mt-0.5">Kode: {{ Auth::user()?->kd_satker ?? '-' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Pesan Info SSO -->
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10">
                    <svg class="w-32 h-32 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="relative z-10">
                    <h3 class="text-lg font-bold text-blue-900 flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Otentikasi Terpusat Aktif
                    </h3>
                    <p class="text-sm text-blue-800 leading-relaxed max-w-2xl">
                        Anda saat ini telah masuk menggunakan akun utama SIPETRA. Anda bisa berpindah ke aplikasi internal BPS lainnya dengan mulus tanpa perlu login ulang (Single Sign-On).
                    </p>

                    @if(Auth::check() && Auth::user()->canAccessPanel(app(\Filament\FilamentManager::class)->getCurrentPanel() ?? filament()->getPanel('admin')))
                    <div class="mt-4">
                        <a href="/admin" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:shadow-outline-blue transition ease-in-out duration-150 shadow-sm">
                            Buka Dasbor Admin SIPETRA &rarr;
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</body>

</html>