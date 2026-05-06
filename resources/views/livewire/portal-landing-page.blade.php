@php
    $accent = $settings->accent_color ?? '#7cbb00';
    $cardBgs = ['#f0fdf4', '#f8fafc', '#fefce8', '#f0f9ff', '#fdf4ff', '#fff7ed', '#f0fdf4', '#f8fafc'];
@endphp

<div class="dot-bg" style="--accent: {{ $accent }}; min-height: 100vh; font-family: 'Inter', sans-serif; color: #0f172a;">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.dot-bg {
    background-color: #f8fafc;
    background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
    background-size: 28px 28px;
}

/* ─── NAVBAR ─── */
.navbar {
    position: sticky; top: 0; z-index: 50;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0;
}
.navbar-inner {
    max-width: 1200px; margin: 0 auto; padding: 0 24px;
    height: 68px; display: flex; align-items: center;
    justify-content: space-between; gap: 16px;
}
.navbar-logo img { height: 36px; width: auto; display: block; }
.navbar-links { display: flex; gap: 32px; list-style: none; }
.navbar-links a { font-size: 14px; font-weight: 500; color: #475569; text-decoration: none; transition: color .15s; }
.navbar-links a:hover, .navbar-links a.active { color: #0f172a; font-weight: 600; }
@media(max-width: 768px){ .navbar-links { display: none; } }

.btn-accent {
    display: inline-flex; align-items: center;
    padding: 10px 22px; background: var(--accent); color: #fff;
    font-size: 14px; font-weight: 600; border-radius: 999px;
    border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s, transform .1s;
    font-family: inherit;
}
.btn-accent:hover { opacity: .88; transform: translateY(-1px); }

/* ─── HERO ─── */
.hero { padding: 72px 24px 56px; text-align: center; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px;
    padding: 4px 14px; font-size: 12px; font-weight: 600; color: #64748b;
    margin-bottom: 20px; letter-spacing: .03em; text-transform: uppercase;
}
.hero-badge span { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--accent); }
.hero h1 {
    font-size: clamp(2.4rem, 5vw, 3.75rem); font-weight: 900;
    line-height: 1.12; color: #0f172a; letter-spacing: -.02em;
    max-width: 700px; margin: 0 auto 16px;
}
.hero h1 em { font-style: normal; color: var(--accent); }
.hero-sub { font-size: 16px; color: #64748b; max-width: 480px; margin: 0 auto 36px; line-height: 1.65; }

/* Search bar */
.search-bar {
    display: flex; align-items: center;
    max-width: 620px; margin: 0 auto;
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 999px;
    padding: 6px 6px 6px 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,.06); gap: 0;
}
.search-bar input {
    flex: 1; border: none; outline: none; font-size: 14px;
    color: #0f172a; background: transparent; padding: 8px 0; font-family: inherit;
}
.search-bar input::placeholder { color: #94a3b8; }
.search-divider { width: 1px; height: 24px; background: #e2e8f0; margin: 0 14px; flex-shrink: 0; }
.search-bar button {
    flex-shrink: 0; padding: 10px 24px; background: var(--accent);
    color: #fff; font-size: 14px; font-weight: 600; border-radius: 999px;
    border: none; cursor: pointer; transition: opacity .15s; font-family: inherit;
}
.search-bar button:hover { opacity: .9; }

/* ─── APPS SECTION ─── */
.apps-section { max-width: 1200px; margin: 0 auto; padding: 0 24px 80px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.section-header h2 { font-size: 20px; font-weight: 700; color: #0f172a; }

.apps-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
}
@media(max-width: 900px){ .apps-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 560px){ .apps-grid { grid-template-columns: 1fr; } }

/* Card */
.app-card {
    display: flex; flex-direction: column;
    border-radius: 20px; border: 1.5px solid #e2e8f0;
    padding: 22px; text-decoration: none; color: inherit;
    transition: box-shadow .2s, border-color .2s, transform .15s;
    background: #fff;
}
.app-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,.1); border-color: var(--accent); transform: translateY(-3px); }
.app-card.accent-card { background: #f0fff4; border-color: #bbf7d0; }
.app-card.accent-card:hover { border-color: var(--accent); }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.card-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.card-domain { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; flex-wrap: wrap; }
.card-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: contain; background: #fff; border: 1px solid #e2e8f0; padding: 5px; flex-shrink: 0; }
.card-logo-placeholder {
    width: 42px; height: 42px; border-radius: 10px; border: 1px solid #e2e8f0;
    background: #f8fafc; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 14px; font-weight: 800; color: var(--accent); letter-spacing: -.5px;
}
.card-desc {
    font-size: 13px; color: #64748b; line-height: 1.6; flex-grow: 1; margin-bottom: 18px;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}
.card-footer { display: flex; align-items: center; justify-content: space-between; }
.card-meta { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #64748b; }
.card-meta-item { display: flex; align-items: center; gap: 3px; }
.btn-pill {
    display: inline-flex; align-items: center; padding: 9px 20px;
    border-radius: 999px; border: 1.5px solid #e2e8f0; background: #fff;
    font-size: 13px; font-weight: 600; color: #0f172a; cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    text-decoration: none; white-space: nowrap; font-family: inherit;
}
.btn-pill:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

/* Footer */
footer { background: #0f172a; color: #94a3b8; padding: 32px 24px; text-align: center; font-size: 13px; }
footer strong { color: #f1f5f9; }
</style>

{{-- ─── NAVBAR ─── --}}
<nav class="navbar">
    <div class="navbar-inner">
        <a href="/" class="navbar-logo">
            <img src="{{ asset('logoBpsDemakOren.png') }}" alt="BPS Kabupaten Demak">
        </a>
        <ul class="navbar-links">
            <li><a href="#" class="active">Beranda</a></li>
            <li><a href="#apps">Semua Aplikasi</a></li>
            <li><a href="{{ route('login') }}">Login</a></li>
        </ul>
        <a href="{{ route('login') }}" class="btn-accent">Masuk Sekarang</a>
    </div>
</nav>

{{-- ─── HERO ─── --}}
<section class="hero" x-data
    @if($settings->background_image)
    style="background-image: url('{{ Storage::url($settings->background_image) }}'); background-size: cover; background-position: center; position: relative;"
    @endif
>

    <h1>{{ $settings->hero_title ?? 'Sipetra' }}<br><em>{{ $settings->hero_accent_title ?? 'All in One Portal' }}</em></h1>

    <p class="hero-sub">
        @if($settings->hero_subtitle)
            {{ $settings->hero_subtitle }}
        @else
            {{ $apps->count() }} aplikasi tersedia &bull; BPS Kabupaten Demak
        @endif
    </p>

    <div class="search-bar">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        &nbsp;
        <input id="search-input" type="text" placeholder="Cari nama aplikasi..."
               @input="$dispatch('search-apps', $event.target.value)">
        <div class="search-divider"></div>
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        &nbsp;
        <input type="text" value="BPS Kab. Demak" style="width:120px;color:#64748b;" readonly>
        <button onclick="document.getElementById('search-input').focus()">Cari</button>
    </div>
</section>

{{-- ─── APPS GRID ─── --}}
<section class="apps-section" id="apps">
    <div class="section-header">
        <h2>Aplikasi BPS Kabupaten Demak</h2>
    </div>

    <div class="apps-grid"
         x-data="{ query: '' }"
         @search-apps.window="query = $event.detail.toLowerCase()">

        @forelse($apps as $i => $app)
        @php
            $bg    = $cardBgs[$i % count($cardBgs)];
            $abbr  = strtoupper(substr($app->name, 0, 2));
            $host  = parse_url($app->url, PHP_URL_HOST) ?? $app->url;
        @endphp

        <a href="{{ $app->url }}"
           target="_blank"
           rel="noopener noreferrer"
           class="app-card {{ $i === 0 ? 'accent-card' : '' }}"
           style="{{ $i !== 0 ? 'background:'.$bg.';' : '' }}"
           x-show="query === '' || '{{ addslashes(strtolower($app->name)) }}'.includes(query) || '{{ addslashes(strtolower($app->description ?? '')) }}'.includes(query)">

            <div class="card-top">
                <div>
                    <div class="card-title">{{ $app->name }}</div>
                    <div class="card-domain">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z"/>
                        </svg>
                        {{ $host }}
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $accent }}">
                            <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>

                @if($app->logo)
                    {{-- Logo diutamakan jika ada --}}
                    <img src="{{ Storage::url($app->logo) }}" alt="{{ $app->name }}" class="card-logo">
                @elseif($app->icon)
                    {{-- Icon heroicon --}}
                    <div class="card-logo-placeholder">
                        <x-dynamic-component
                            :component="$app->icon"
                            style="width:22px;height:22px;color:{{ $accent }}"
                        />
                    </div>
                @else
                    {{-- Fallback: singkatan nama --}}
                    <div class="card-logo-placeholder">{{ $abbr }}</div>
                @endif
            </div>

            <p class="card-desc">{{ $app->description ?? 'Sistem informasi ' . $app->name . ' BPS Kabupaten Demak.' }}</p>

            <div class="card-footer">
                <span class="btn-pill">Buka Aplikasi</span>
            </div>
        </a>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:48px 24px;color:#64748b;background:#fff;border-radius:20px;border:1.5px dashed #e2e8f0;">
            <p>Belum ada aplikasi yang ditambahkan.</p>
        </div>
        @endforelse
    </div>
</section>

<footer>
    <strong>BPS Kabupaten Demak</strong> &mdash; Sipetra Portal &copy; {{ date('Y') }}
</footer>

</div>
