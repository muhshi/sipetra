<div class="min-h-screen bg-slate-50 font-sans text-slate-900" style="--accent: {{ $settings->accent_color ?? '#06b6d4' }};">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <img src="{{ asset('logoBpsDemakOren.png') }}" alt="Logo" class="h-8 w-auto">
                    <span class="ml-2 font-bold text-xl text-slate-800">Sipetra Portal</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-white pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="absolute inset-0">
            <div class="bg-slate-50 h-1/3 sm:h-2/3"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl tracking-tight font-extrabold text-slate-900 sm:text-5xl md:text-6xl">
                    {{ $settings->hero_title ?? 'Aplikasi Sipetra' }}
                </h1>
                @if($settings->hero_subtitle)
                <p class="mt-3 max-w-md mx-auto text-base text-slate-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    {{ $settings->hero_subtitle }}
                </p>
                @endif
                
                <!-- Optional Search Box (Visual only for now or simple JS filter) -->
                <div class="mt-10 max-w-xl mx-auto">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" x-data @input="$dispatch('filter-apps', $event.target.value)" class="block w-full pl-10 pr-3 py-4 border border-slate-300 rounded-full leading-5 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:border-transparent sm:text-sm shadow-sm transition duration-150 ease-in-out" style="focus:ring-color: var(--accent);" placeholder="Cari aplikasi...">
                        <button class="absolute inset-y-1 right-1 px-6 py-2 border border-transparent text-sm leading-5 font-medium rounded-full text-white transition duration-150 ease-in-out" style="background-color: var(--accent); hover:opacity-90;">
                            Cari
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Apps Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-8 flex justify-between items-center border-b border-slate-200 pb-4">
            <h2 class="text-2xl font-bold text-slate-900">One Platform Many Solutions</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" x-data="{ filter: '' }" @filter-apps.window="filter = $event.detail.toLowerCase()">
            @forelse($apps as $app)
                <a href="{{ $app->url }}" 
                   class="flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md hover:border-slate-300 transition-all duration-200 group relative overflow-hidden"
                   x-show="filter === '' || '{{ strtolower($app->name) }}'.includes(filter) || '{{ strtolower($app->description) }}'.includes(filter)"
                   style="--hover-accent: {{ $settings->accent_color ?? '#06b6d4' }};"
                   x-init="$el.addEventListener('mouseenter', () => $el.style.borderColor = 'var(--hover-accent)'); $el.addEventListener('mouseleave', () => $el.style.borderColor = '');"
                   >
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2">
                            @if($app->logo)
                                <img src="{{ Storage::url($app->logo) }}" alt="{{ $app->name }}" class="max-h-full max-w-full object-contain">
                            @else
                                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            Aplikasi
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-slate-900 mb-2 group-hover:text-accent transition-colors" x-init="$el.addEventListener('mouseenter', () => $el.style.color = 'var(--hover-accent)'); $el.addEventListener('mouseleave', () => $el.style.color = '');">
                        {{ $app->name }}
                    </h3>
                    
                    <p class="text-sm text-slate-500 flex-grow mb-6 line-clamp-3">
                        {{ $app->description ?? 'Deskripsi aplikasi tidak tersedia.' }}
                    </p>
                    
                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-900 group-hover:text-accent" x-init="$el.addEventListener('mouseenter', () => $el.style.color = 'var(--hover-accent)'); $el.addEventListener('mouseleave', () => $el.style.color = '');">
                            Buka Aplikasi
                        </span>
                        <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-accent transition-colors" x-init="$el.addEventListener('mouseenter', () => { $el.style.backgroundColor = 'var(--hover-accent)'; $el.style.color = 'white'; }); $el.addEventListener('mouseleave', () => { $el.style.backgroundColor = ''; $el.style.color = ''; });">
                            <svg class="h-4 w-4 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 bg-white rounded-2xl border border-slate-200 border-dashed">
                    <svg class="mx-auto h-12 w-12 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p>Belum ada aplikasi yang ditambahkan.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
