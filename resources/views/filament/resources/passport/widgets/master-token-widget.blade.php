<x-filament-widgets::widget>
    @if($token)
    <x-filament::section>
        <div class="flex items-center gap-2 mb-4">
            <x-filament::icon
                icon="heroicon-o-check-circle"
                class="w-6 h-6 text-success-500"
            />
            <h2 class="text-lg font-bold">Master API Token Berhasil Dibuat!</h2>
        </div>

        <p class="text-sm text-gray-500 mb-4">
            Silakan salin token di bawah ini dan simpan di <code>.env</code> Aplikasi Client. Token ini <strong>tidak akan ditampilkan lagi</strong> setelah halaman direfresh.
        </p>

        <div class="flex items-center gap-4">
            <input 
                type="text" 
                readonly 
                value="{{ $token }}" 
                class="w-full bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg shadow-sm font-mono text-sm p-3 focus:ring-primary-500 focus:border-primary-500" 
                id="master-token-input" 
            />
            
            <x-filament::button
                color="primary"
                icon="heroicon-o-clipboard-document"
                x-data
                x-on:click="
                    navigator.clipboard.writeText(document.getElementById('master-token-input').value);
                    $tooltip('Token berhasil disalin!');
                "
            >
                Copy
            </x-filament::button>
        </div>
    </x-filament::section>
    @endif
</x-filament-widgets::widget>
