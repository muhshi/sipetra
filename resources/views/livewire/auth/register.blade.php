<div class="w-full max-w-md p-8 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 dark:bg-gray-800/90 dark:border-gray-700">
    <div class="text-center mb-6">
        <img src="{{ asset('logoBpsDemakHitam.png') }}" alt="BPS Demak"
            class="h-16 w-auto mx-auto mb-4 object-contain dark:hidden">
        <img src="{{ asset('logoBpsDemakOren.png') }}" alt="BPS Demak"
            class="hidden h-16 w-auto mx-auto mb-4 object-contain dark:block">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Daftar Akun Baru</h2>
        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Pendaftaran Akun Mitra & Pengguna Layanan SIPETRA</p>
    </div>

    <form wire:submit="register" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return false; } submitting = true;">
        <!-- Input Nama Lengkap -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input wire:model="name" id="name" type="text" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Nama lengkap Anda" required autofocus>
            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Email <span class="text-red-500">*</span></label>
            <input wire:model="email" id="email" type="email" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="email@contoh.com" required>
            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Nomor Telepon / WA -->
        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor WhatsApp / HP</label>
            <input wire:model="phone" id="phone" type="tel" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="08xxxxxxxxxx">
            @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input SOBAT ID (Opsional) -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
                <label for="sobat_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SOBAT ID</label>
                <span class="text-xs text-gray-400">Opsional untuk Mitra BPS</span>
            </div>
            <input wire:model="sobat_id" id="sobat_id" type="text" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Contoh: 3321xxxx">
            @error('sobat_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
            <input wire:model="password" id="password" type="password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Minimal 8 karakter" required>
            @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Konfirmasi Password -->
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Ulangi password baru" required>
        </div>

        <button type="submit"
            wire:loading.attr="disabled"
            wire:target="register"
            x-bind:disabled="submitting"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex justify-center items-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <span wire:loading.remove wire:target="register">Daftar Akun</span>
            <span wire:loading wire:target="register" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses Pendaftaran...
            </span>
        </button>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">Masuk di sini</a>
            </p>
        </div>
    </form>

    <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-5 text-center">
        <p class="text-xs text-gray-400">
            &copy; {{ date('Y') }} Badan Pusat Statistik.<br>Akun Mitra Terintegrasi dengan Ekosistem BPS.
        </p>
    </div>
</div>
