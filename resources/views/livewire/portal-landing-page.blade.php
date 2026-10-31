@php
    $accent = $settings->accent_color ?? '#0284c7';
    $cardBgs = ['#f0fdf4', '#f8fafc', '#fefce8', '#f0f9ff', '#fdf4ff', '#fff7ed'];
@endphp

<div 
    class="dot-bg" 
    style="--accent: {{ $accent }}; min-height: 100vh; font-family: 'Inter', sans-serif; color: #0f172a;"
    x-data="{
        spotlightOpen: false,
        spotlightQuery: '',
        selectedCategory: 'all',
        query: '',
        openSpotlight() {
            this.spotlightOpen = true;
            this.$nextTick(() => {
                const el = document.getElementById('spotlight-search-input');
                if (el) el.focus();
            });
        },
        closeSpotlight() {
            this.spotlightOpen = false;
            this.spotlightQuery = '';
        }
    }"
    x-on:keydown.window.ctrl.k.prevent="openSpotlight()"
    x-on:keydown.window.cmd.k.prevent="openSpotlight()"
    x-on:keydown.escape.window="closeSpotlight()"
>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.dot-bg {
    background-color: #f8fafc;
    background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
    background-size: 28px 28px;
}

/* ─── NAVBAR ─── */
.navbar {
    position: sticky; top: 0; z-index: 40;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0;
}
.navbar-inner {
    max-width: 1200px; margin: 0 auto; padding: 0 24px;
    height: 70px; display: flex; align-items: center;
    justify-content: space-between; gap: 16px;
}
.navbar-logo img { height: 38px; width: auto; display: block; }

.navbar-center {
    display: flex; align-items: center; gap: 16px;
}

.spotlight-trigger {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; background: #f1f5f9; border: 1px solid #cbd5e1;
    border-radius: 999px; font-size: 13px; color: #64748b;
    cursor: pointer; transition: all .15s; font-family: inherit;
}
.spotlight-trigger:hover { background: #e2e8f0; color: #0f172a; }
.spotlight-trigger kbd {
    font-size: 11px; font-weight: 700; background: #fff;
    border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 6px;
    color: #475569;
}

.user-nav-chip {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 4px 12px 4px 6px; background: #fff;
    border: 1px solid #e2e8f0; border-radius: 999px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}

.btn-accent {
    display: inline-flex; align-items: center;
    padding: 9px 20px; background: var(--accent); color: #fff;
    font-size: 13px; font-weight: 600; border-radius: 999px;
    border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s, transform .1s;
    font-family: inherit;
}
.btn-accent:hover { opacity: .9; transform: translateY(-1px); }

/* ─── HERO ─── */
.hero { padding: 64px 24px 44px; text-align: center; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px;
    padding: 4px 14px; font-size: 12px; font-weight: 600; color: #64748b;
    margin-bottom: 20px; letter-spacing: .03em; text-transform: uppercase;
}
.hero-badge span { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--accent); }
.hero h1 {
    font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 900;
    line-height: 1.15; color: #0f172a; letter-spacing: -.02em;
    max-width: 700px; margin: 0 auto 16px;
}
.hero h1 em { font-style: normal; color: var(--accent); }
.hero-sub { font-size: 15px; color: #64748b; max-width: 480px; margin: 0 auto 32px; line-height: 1.6; }

/* Search bar */
.search-bar {
    display: flex; align-items: center;
    max-width: 600px; margin: 0 auto;
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 999px;
    padding: 5px 6px 5px 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,.05); gap: 8px;
}
.search-bar input {
    flex: 1; border: none; outline: none; font-size: 14px;
    color: #0f172a; background: transparent; padding: 8px 0; font-family: inherit;
}
.search-bar input::placeholder { color: #94a3b8; }
.search-bar button {
    flex-shrink: 0; padding: 9px 22px; background: var(--accent);
    color: #fff; font-size: 13px; font-weight: 600; border-radius: 999px;
    border: none; cursor: pointer; transition: opacity .15s; font-family: inherit;
}

/* ─── CATEGORY PILLS ─── */
.category-filter-bar {
    display: flex; align-items: center; justify-content: center;
    flex-wrap: wrap; gap: 8px; margin: 36px 0 28px;
}
.cat-pill {
    padding: 7px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .15s; border: 1px solid #e2e8f0;
    background: #fff; color: #64748b; font-family: inherit;
}
.cat-pill:hover { border-color: #cbd5e1; color: #0f172a; background: #f8fafc; }
.cat-pill.active {
    background: #0f172a; color: #fff; border-color: #0f172a;
}

/* ─── APPS SECTION ─── */
.apps-section { max-width: 1200px; margin: 0 auto; padding: 0 24px 80px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.section-header h2 { font-size: 18px; font-weight: 800; color: #0f172a; }

.apps-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
}
@media(max-width: 900px){ .apps-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 560px){ .apps-grid { grid-template-columns: 1fr; } }

/* Card */
.app-card {
    display: flex; flex-direction: column;
    border-radius: 20px; border: 1px solid #e2e8f0;
    padding: 22px; text-decoration: none; color: inherit;
    transition: all .25s cubic-bezier(.4,0,.2,1);
    background: #fff; position: relative; overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
}
.app-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--accent); opacity: 0; transition: opacity .25s;
}
.app-card:hover {
    box-shadow: 0 16px 25px -5px rgba(0, 0, 0, 0.06);
    border-color: #cbd5e1; transform: translateY(-3px);
}
.app-card:hover::before { opacity: 1; }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 14px; }
.card-header-text { flex: 1; min-width: 0; }
.card-category-tag {
    font-size: 11px; font-weight: 600; color: #64748b;
    text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px; display: block;
}
.card-title { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 2px; }
.card-subtitle { font-size: 12px; font-weight: 500; color: #64748b; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.card-logo-container {
    width: 44px; height: 44px; border-radius: 12px;
    background: #f8fafc; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 1px solid #e2e8f0;
}
.card-logo { width: 100%; height: 100%; border-radius: 11px; object-fit: contain; padding: 5px; }

.card-desc {
    font-size: 13px; color: #475569; line-height: 1.55; flex-grow: 1; margin-bottom: 20px;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}

.card-footer { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: auto; }
.card-domain { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: #64748b; }

.health-dot {
    width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0;
}
.health-online { background-color: #10b981; box-shadow: 0 0 0 2px #d1fae5; }
.health-warning { background-color: #f59e0b; box-shadow: 0 0 0 2px #fef3c7; }
.health-offline { background-color: #ef4444; box-shadow: 0 0 0 2px #fee2e2; }

.btn-pill {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9;
    color: #0f172a; transition: all .2s;
}
.app-card:hover .btn-pill { background: var(--accent); color: #fff; transform: translateX(3px); }

/* Footer */
footer { background: #0f172a; color: #94a3b8; padding: 28px 24px; text-align: center; font-size: 13px; }
footer strong { color: #f1f5f9; }
</style>

{{-- ─── LIVEWIRE SELF-SERVICE USER PROFILE MODAL ─── --}}
<livewire:account.user-profile-modal />

{{-- ─── NAVBAR ─── --}}
<nav class="navbar">
    <div class="navbar-inner">
        <a href="/" class="navbar-logo">
            <img src="{{ asset('logoBpsDemakOren.png') }}" alt="BPS Kabupaten Demak">
        </a>

        <div class="navbar-center">
            <button type="button" @click="openSpotlight()" class="spotlight-trigger">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/>
                </svg>
                <span>Cari cepat...</span>
                <kbd>Ctrl K</kbd>
            </button>
        </div>

        <div class="flex items-center space-x-3">
            @auth
                <div class="user-nav-chip">
                    <div class="w-7 h-7 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-xs font-bold text-slate-700">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->getFilamentAvatarUrl() }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-slate-800 max-w-[130px] truncate" title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name }}
                    </span>
                    <button 
                        type="button" 
                        @click="$dispatch('open-user-profile')"
                        class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition"
                        title="Buka pengaturan profil & keamanan"
                    >
                        Profil Akun
                    </button>
                </div>

                <a href="/admin" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition px-2">
                    Admin
                </a>

                <form action="{{ route('oauth.logout') }}" method="GET" class="m-0">
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Logout dari SIPETRA">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            @else
                <a href="/admin" class="btn-accent">
                    Masuk Sekarang
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ─── HERO ─── --}}
<section class="hero"
    @if($settings->background_image)
    style="background-image: url('{{ Storage::url($settings->background_image) }}'); background-size: cover; background-position: center; position: relative;"
    @endif
>
    <div class="hero-badge">
        <span></span> Single Sign-On BPS Demak
    </div>

    <h1>{{ $settings->hero_title ?? 'Sipetra' }}<br><em>{{ $settings->hero_accent_title ?? 'All in One Portal' }}</em></h1>

    <p class="hero-sub">
        @if($settings->hero_subtitle)
            {{ $settings->hero_subtitle }}
        @else
            {{ $apps->count() }} aplikasi terintegrasi SSO &bull; BPS Kabupaten Demak
        @endif
    </p>

    <div class="search-bar">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input 
            type="text" 
            placeholder="Cari nama atau kepanjangan aplikasi..."
            x-model="query"
        >
        <button type="button" @click="openSpotlight()">Pencarian Cepat</button>
    </div>

    {{-- Category Pills --}}
    <div class="category-filter-bar">
        <button 
            type="button" 
            class="cat-pill" 
            :class="{ 'active': selectedCategory === 'all' }"
            @click="selectedCategory = 'all'"
        >
            Semua ({{ $apps->count() }})
        </button>

        @foreach($categories as $cat)
            @php
                $catCount = $apps->where('category', $cat)->count();
            @endphp
            <button 
                type="button" 
                class="cat-pill" 
                :class="{ 'active': selectedCategory === '{{ addslashes($cat) }}' }"
                @click="selectedCategory = '{{ addslashes($cat) }}'"
            >
                {{ $cat }} ({{ $catCount }})
            </button>
        @endforeach
    </div>
</section>

{{-- ─── APPS GRID ─── --}}
<section class="apps-section" id="apps">
    <div class="section-header">
        <h2>Katalog Layanan & Aplikasi</h2>
    </div>

    <div class="apps-grid">
        @forelse($apps as $i => $app)
            @php
                $abbr = strtoupper(substr($app->name, 0, 2));
                $host = parse_url($app->url, PHP_URL_HOST) ?? $app->url;
                $healthClass = match ($app->health_status ?? 'online') {
                    'warning' => 'health-warning',
                    'offline' => 'health-offline',
                    default => 'health-online',
                };
            @endphp

            <a 
                href="{{ $app->url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="app-card"
                x-show="(selectedCategory === 'all' || selectedCategory === '{{ addslashes($app->category ?? 'Umum & Kepegawaian') }}') && (query === '' || '{{ addslashes(strtolower($app->name)) }}'.includes(query.toLowerCase()) || '{{ addslashes(strtolower($app->full_name ?? '')) }}'.includes(query.toLowerCase()) || '{{ addslashes(strtolower($app->description ?? '')) }}'.includes(query.toLowerCase()))"
            >
                <div class="card-top">
                    <div class="card-header-text">
                        <span class="card-category-tag">{{ $app->category ?? 'Umum & Kepegawaian' }}</span>
                        <div class="card-title">{{ $app->name }}</div>
                        @if($app->full_name)
                            <div class="card-subtitle">{{ $app->full_name }}</div>
                        @endif
                    </div>

                    <div class="card-logo-container">
                        @if($app->logo)
                            <img src="{{ Storage::url($app->logo) }}" alt="{{ $app->name }}" class="card-logo">
                        @elseif($app->icon)
                            <x-dynamic-component
                                :component="$app->icon"
                                style="width:22px;height:22px;color:{{ $accent }}"
                            />
                        @else
                            <span style="font-weight:800;color:{{ $accent }};font-size:14px">{{ $abbr }}</span>
                        @endif
                    </div>
                </div>

                <p class="card-desc">{{ $app->description ?? 'Sistem informasi ' . $app->name . ' BPS Kabupaten Demak.' }}</p>

                <div class="card-footer">
                    <div class="card-domain">
                        <span class="health-dot {{ $healthClass }}" title="Status: {{ ucfirst($app->health_status ?? 'online') }}"></span>
                        {{ $host }}
                    </div>
                    <div class="btn-pill">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:48px 24px;color:#64748b;background:#fff;border-radius:20px;border:1.5px dashed #e2e8f0;">
                <p>Belum ada aplikasi yang ditambahkan.</p>
            </div>
        @endforelse
    </div>
</section>

{{-- ─── SPOTLIGHT COMMAND PALETTE (CTRL+K) ─── --}}
<div 
    x-show="spotlightOpen" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20"
    role="dialog" 
    aria-modal="true"
>
    <!-- Backdrop -->
    <div 
        x-show="spotlightOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="closeSpotlight()"
    ></div>

    <!-- Palette Box -->
    <div 
        x-show="spotlightOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="mx-auto max-w-xl transform divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 transition-all relative z-10"
    >
        <div class="relative flex items-center px-4 py-3">
            <svg class="h-5 w-5 text-slate-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/>
            </svg>
            <input 
                id="spotlight-search-input"
                type="text" 
                x-model="spotlightQuery"
                class="h-10 w-full border-0 bg-transparent pr-4 text-sm text-slate-800 placeholder-slate-400 focus:outline-none"
                placeholder="Ketik nama aplikasi untuk berpindah instan..."
            >
            <button 
                type="button" 
                @click="closeSpotlight()" 
                class="text-xs px-2 py-1 bg-slate-100 text-slate-500 rounded-md hover:bg-slate-200"
            >
                ESC
            </button>
        </div>

        <ul class="max-h-80 scroll-py-2 overflow-y-auto p-2 text-sm text-slate-700">
            @foreach($apps as $app)
                <li 
                    x-show="spotlightQuery === '' || '{{ addslashes(strtolower($app->name)) }}'.includes(spotlightQuery.toLowerCase()) || '{{ addslashes(strtolower($app->full_name ?? '')) }}'.includes(spotlightQuery.toLowerCase()) || '{{ addslashes(strtolower($app->description ?? '')) }}'.includes(spotlightQuery.toLowerCase())"
                    class="group flex cursor-pointer items-center justify-between rounded-xl px-3 py-2.5 hover:bg-slate-100/80 transition"
                >
                    <a href="{{ $app->url }}" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-3 w-full">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700">
                            {{ strtoupper(substr($app->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <span>{{ $app->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 font-normal">
                                    {{ $app->category ?? 'Umum' }}
                                </span>
                            </div>
                            @if($app->full_name)
                                <div class="text-xs text-slate-400 truncate">{{ $app->full_name }}</div>
                            @endif
                        </div>
                        <svg class="h-4 w-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center justify-between bg-slate-50 px-4 py-2 text-[11px] text-slate-400">
            <span>Gunakan <strong>Enter</strong> untuk membuka aplikasi</span>
            <span>Tekan <strong>Esc</strong> untuk menutup</span>
        </div>
    </div>
</div>

<footer>
    <strong>BPS Kabupaten Demak</strong> &mdash; Sipetra Portal &copy; {{ date('Y') }}
</footer>

</div>
