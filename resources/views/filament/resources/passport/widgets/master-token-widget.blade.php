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

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Silakan salin token di bawah ini dan simpan di <code>.env</code> Aplikasi Client. Token ini <strong>tidak akan ditampilkan lagi</strong> setelah halaman direfresh.
        </p>

        <div class="rounded-xl overflow-hidden shadow-sm border border-gray-800 bg-[#1e1e1e]">
            <div class="flex justify-between items-center px-4 py-2 bg-[#2d2d2d] border-b border-gray-800">
                <span class="text-xs font-semibold text-gray-400 tracking-wider">BEARER TOKEN</span>
                <div class="flex items-center">
                    <button 
                        type="button"
                        x-data="{ copied: false }"
                        x-on:click="
                            navigator.clipboard.writeText('{{ $token }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="text-gray-400 hover:text-white transition-colors focus:outline-none flex items-center gap-1.5 text-xs"
                    >
                        <span x-show="!copied" class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-o-clipboard" class="w-4 h-4" />
                            Copy token
                        </span>
                        <span x-show="copied" style="display: none;" class="flex items-center gap-1.5 text-success-400">
                            <x-filament::icon icon="heroicon-o-check" class="w-4 h-4" />
                            Copied!
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="p-4 overflow-x-auto">
                <code class="font-mono text-sm text-[#e6c07b] break-all select-all">{{ $token }}</code>
            </div>
        </div>
    </x-filament::section>
    @endif
</x-filament-widgets::widget>
