<div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gray-100">
    <div class="flex flex-col items-center mb-8">
        <!-- Logo BPS Demak -->
        <div class="mb-4">
            <img src="{{ asset('logoBpsDemakHitam.png') }}" alt="Logo BPS Demak" class="h-16 w-auto object-contain">
        </div>
        <h2 class="text-2xl font-bold text-gray-800 text-center">Single Sign-On</h2>
        <p class="text-sm text-gray-500 mt-1">Sistem Informasi Pegawai Terpadu (SIPETRA)</p>
    </div>

    <!-- Toggle Login Type -->
    <div class="flex p-1 space-x-1 bg-gray-100 rounded-lg mb-6">
        <button wire:click="$set('loginType', 'nip')"
            class="flex-1 py-2 text-sm font-medium rounded-md transition-all {{ $loginType === 'nip' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
            NIP
        </button>
        <button wire:click="$set('loginType', 'email')"
            class="flex-1 py-2 text-sm font-medium rounded-md transition-all {{ $loginType === 'email' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
            Email
        </button>
    </div>

    <form wire:submit="authenticate">
        <!-- Input NIP -->
        @if($loginType === 'nip')
        <div class="mb-4">
            <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP Pegawai</label>
            <input wire:model="nip" id="nip" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Masukkan 9 atau 18 digit NIP" required autofocus>
            @error('nip') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        @endif

        <!-- Input Email -->
        @if($loginType === 'email')
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
            <input wire:model="email" id="email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="contoh@gmail.com" required autofocus>
            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        @endif

        <!-- Input Password -->
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input wire:model="password" id="password" type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="••••••••" required>
            @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Ingat Saya</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition-colors flex justify-center items-center">
            <span wire:loading.remove wire:target="authenticate">Masuk Sistem</span>
            <span wire:loading wire:target="authenticate" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            </span>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-xs text-gray-400">
            &copy; {{ date('Y') }} Badan Pusat Statistik.<br>Terintegrasi dengan Layanan Kepegawaian & SOBAT.
        </p>
    </div>
</div>