<x-filament-widgets::widget>
    @if($token)
    <div style="margin-bottom: 2rem;">
        <x-filament::section>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <x-filament::icon
                    icon="heroicon-o-check-circle"
                    style="width: 1.5rem; height: 1.5rem; color: #10b981;"
                />
                <h2 style="font-size: 1.125rem; font-weight: 700;">Master API Token Berhasil Dibuat!</h2>
            </div>

            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem;">
                Silakan salin token di bawah ini dan simpan di <code>.env</code> Aplikasi Client. Token ini <strong>tidak akan ditampilkan lagi</strong> setelah halaman direfresh.
            </p>

            <div style="border-radius: 0.75rem; overflow: hidden; border: 1px solid #374151; background-color: #1e1e1e; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                <!-- Header Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem; background-color: #2d2d2d; border-bottom: 1px solid #374151;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: #9ca3af; letter-spacing: 0.05em;">BEARER TOKEN</span>
                    
                    <div x-data="{ copied: false }">
                        <button 
                            type="button"
                            x-on:click="
                                navigator.clipboard.writeText('{{ $token }}');
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            style="color: #9ca3af; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; outline: none; transition: color 0.2s;"
                            onmouseover="this.style.color='#ffffff'"
                            onmouseout="this.style.color='#9ca3af'"
                        >
                            <span x-show="!copied" style="display: flex; align-items: center; gap: 0.375rem;">
                                <x-filament::icon icon="heroicon-o-clipboard" style="width: 1rem; height: 1rem;" />
                                Copy token
                            </span>
                            <span x-show="copied" style="display: none; align-items: center; gap: 0.375rem; color: #10b981;">
                                <x-filament::icon icon="heroicon-o-check" style="width: 1rem; height: 1rem;" />
                                Copied!
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Token Body -->
                <div x-data="{ expanded: false }" style="position: relative; background-color: #1e1e1e;">
                    <div :style="expanded ? '' : 'max-height: 80px; overflow: hidden; position: relative;'">
                        <div style="padding: 1rem;">
                            <code style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 0.875rem; color: #e6c07b; word-break: break-all; white-space: pre-wrap; display: block; line-height: 1.5;">{{ $token }}</code>
                        </div>
                        
                        <!-- Gradient Overlay when collapsed -->
                        <div x-show="!expanded" style="position: absolute; bottom: 0; left: 0; right: 0; height: 40px; background: linear-gradient(transparent, #1e1e1e); pointer-events: none;"></div>
                    </div>
                    
                    <div style="display: flex; justify-content: center; padding: 0.5rem; border-top: 1px solid #374151; background-color: #262626;">
                        <button 
                            type="button" 
                            x-on:click="expanded = !expanded"
                            style="background: none; border: none; color: #9ca3af; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; outline: none; transition: color 0.2s;"
                            onmouseover="this.style.color='#ffffff'"
                            onmouseout="this.style.color='#9ca3af'"
                        >
                            <span x-show="!expanded">See more</span>
                            <span x-show="expanded" style="display: none;">Show less</span>
                            <x-filament::icon 
                                icon="heroicon-o-chevron-down" 
                                style="width: 0.875rem; height: 0.875rem; transition: transform 0.3s;"
                                ::style="expanded ? 'transform: rotate(180deg); width: 0.875rem; height: 0.875rem;' : 'width: 0.875rem; height: 0.875rem;'"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
    @endif
</x-filament-widgets::widget>
