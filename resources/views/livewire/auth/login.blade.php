<div class="w-full max-w-md p-8 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 dark:bg-gray-800/90 dark:border-gray-700">
    <div class="text-center mb-8">
        <img src="{{ asset('logoBpsDemakHitam.png') }}" alt="BPS Demak"
            class="h-16 w-auto mx-auto mb-4 object-contain dark:hidden">
        <img src="{{ asset('logoBpsDemakOren.png') }}" alt="BPS Demak"
            class="hidden h-16 w-auto mx-auto mb-4 object-contain dark:block">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Single Sign-On (SSO)</h2>
        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Sistem Informasi Pegawai Terpadu (SIPETRA)</p>
    </div>

    <form wire:submit="authenticate" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return false; } submitting = true;">
        <!-- Input Username/NIP/Email/SOBAT ID -->
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIP / Email / Username / SOBAT ID</label>
            <input wire:model="username" id="username" type="text" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Masukkan NIP, Email, Username, atau SOBAT ID" required autofocus>
            @error('username') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Password -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            </div>
            <input wire:model="password" id="password" type="password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="••••••••" required>
            @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Remember Me & Submit -->
        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center cursor-pointer">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ingat Saya</span>
            </label>
        </div>

        <button type="submit"
            wire:loading.attr="disabled"
            wire:target="authenticate"
            x-bind:disabled="submitting"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex justify-center items-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <span wire:loading.remove wire:target="authenticate">Masuk ke Akun</span>
            <span wire:loading wire:target="authenticate" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            </span>
        </button>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Belum memiliki akun Mitra?
                <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">Daftar sekarang</a>
            </p>
        </div>
    </form>

    <div class="mt-8 border-t border-gray-100 dark:border-gray-700 pt-6 text-center">
        <p class="text-xs text-gray-400">
            &copy; {{ date('Y') }} Badan Pusat Statistik.<br>Terintegrasi dengan Layanan Kepegawaian & SOBAT.
        </p>
    </div>
</div>
