@php
    $accent = $settings->accent_color ?? '#7cbb00';
    $accentDark = $settings->accent_color ?? '#6aa800';

    /**
     * Generate a soft pastel background for a card based on its index.
     * Mirrors the AZIFA reference where the first card has a tinted background.
     */
    $cardBgs = ['#f0fdf4', '#f8fafc', '#fefce8', '#f0f9ff', '#fdf4ff', '#fff7ed', '#f0fdf4', '#f8fafc'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->hero_title ?? 'Sipetra Portal' }} — BPS Kabupaten Demak</title>
    <meta name="description" content="{{ $settings->hero_subtitle ?? 'Portal aplikasi BPS Kabupaten Demak' }}">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: {{ $accent }};
            --accent-dark: {{ $accentDark }};
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }

        /* ── Subtle dot grid background ── */
        .dot-bg {
            background-color: #f8fafc;
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ─────────── NAVBAR ─────────── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
        }
        .navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .navbar-logo { display: flex; align-items: center; gap: 0; flex-shrink: 0; }
        .navbar-logo img { height: 36px; width: auto; }
        .navbar-links { display: flex; gap: 32px; list-style: none; }
        .navbar-links a {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            transition: color .15s;
        }
        .navbar-links a:hover { color: #0f172a; }
        .navbar-links a.active { color: #0f172a; font-weight: 600; }

        .btn-accent {
            display: inline-flex;
            align-items: center;
            padding: 10px 22px;
            background: var(--accent);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s, transform .1s;
        }
        .btn-accent:hover { opacity: .9; transform: translateY(-1px); }

        @media (max-width: 768px) {
            .navbar-links { display: none; }
        }

        /* ─────────── HERO ─────────── */
        .hero {
            padding: 72px 24px 56px;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 20px;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .hero-badge span { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--accent); }

        .hero h1 {
            font-size: clamp(2.4rem, 5vw, 3.75rem);
            font-weight: 900;
            line-height: 1.12;
            color: #0f172a;
            letter-spacing: -.02em;
            max-width: 700px;
            margin: 0 auto 16px;
        }
        .hero h1 em { font-style: normal; color: var(--accent); }

        .hero-sub {
            font-size: 16px;
            color: #64748b;
            max-width: 480px;
            margin: 0 auto 36px;
            line-height: 1.65;
        }

        /* Search bar — two-input style from reference */
        .search-bar {
            display: flex;
            align-items: center;
            max-width: 620px;
            margin: 0 auto;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 999px;
            padding: 6px 6px 6px 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
            gap: 0;
        }
        .search-bar input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 14px;
            color: #0f172a;
            background: transparent;
            padding: 8px 0;
            font-family: inherit;
        }
        .search-bar input::placeholder { color: #94a3b8; }
        .search-divider { width: 1px; height: 24px; background: #e2e8f0; margin: 0 14px; flex-shrink: 0; }
        .search-bar button {
            flex-shrink: 0;
            padding: 10px 24px;
            background: var(--accent);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
        }
        .search-bar button:hover { opacity: .9; }

        /* ─────────── APPS SECTION ─────────── */
        .apps-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .section-header h2 { font-size: 20px; font-weight: 700; color: #0f172a; }
        .section-header a { font-size: 14px; font-weight: 500; color: var(--accent); text-decoration: none; }
        .section-header a:hover { text-decoration: underline; }

        /* Grid */
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        @media (max-width: 900px) { .apps-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .apps-grid { grid-template-columns: 1fr; } }

        /* Card */
        .app-card {
            display: flex;
            flex-direction: column;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            padding: 22px;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .2s, border-color .2s, transform .15s;
            background: #fff;
            position: relative;
            overflow: hidden;
        }
        .app-card:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,.1);
            border-color: var(--accent);
            transform: translateY(-3px);
        }

        /* Top row inside card */
        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        .card-title-block {}
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .card-domain {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #64748b;
        }
        .card-domain svg { flex-shrink: 0; }
        .card-logo {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 5px;
            flex-shrink: 0;
        }
        .card-logo-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--accent);
        }

        .card-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 18px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: #64748b;
        }
        .card-meta-item { display: flex; align-items: center; gap: 4px; }
        .card-meta-item svg { flex-shrink: 0; }

        .btn-pill {
            display: inline-flex;
            align-items: center;
            padding: 9px 20px;
            border-radius: 999px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-pill:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        /* First card accent background */
        .app-card.accent-card { background: #f0fff4; border-color: #bbf7d0; }
        .app-card.accent-card:hover { border-color: var(--accent); }

        /* ─────────── FOOTER ─────────── */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 32px 24px;
            text-align: center;
            font-size: 13px;
        }
        footer strong { color: #f1f5f9; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="dot-bg">

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
    <section class="hero" x-data>
        <div class="hero-badge">
            <span></span>
            Satu Portal, Banyak Solusi
        </div>

        <h1>{{ $settings->hero_title ?? 'Temukan Aplikasi' }}<br><em>Yang Anda Butuhkan</em></h1>

        @if($settings->hero_subtitle)
        <p class="hero-sub">{{ $settings->hero_subtitle }}</p>
        @else
        <p class="hero-sub">{{ $apps->count() }} aplikasi tersedia &bull; BPS Kabupaten Demak</p>
        @endif

        {{-- Search Bar --}}
        <div class="search-bar">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            &nbsp;
            <input id="search-input" type="text" placeholder="Cari nama atau deskripsi aplikasi..."
                   @input="$dispatch('search-apps', $event.target.value)">
            <div class="search-divider"></div>
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            &nbsp;
            <input type="text" value="BPS Kab. Demak" style="width:120px; color:#64748b;" readonly>
            <button onclick="document.getElementById('search-input').focus()">Cari</button>
        </div>
    </section>

    {{-- ─── APPS GRID ─── --}}
    <section class="apps-section" id="apps">
        <div class="section-header">
            <h2>Aplikasi BPS Kabupaten Demak</h2>
            <a href="#apps">Lihat Semua &rarr;</a>
        </div>

        <div class="apps-grid"
             x-data="{ query: '' }"
             @search-apps.window="query = $event.detail.toLowerCase()">

            @forelse($apps as $i => $app)
            @php
                $bg   = $cardBgs[$i % count($cardBgs)];
                $abbr = strtoupper(substr($app->name, 0, 2));
                $isFirst = $i === 0;
                $domain = parse_url($app->url, PHP_URL_HOST) ?? $app->url;
            @endphp

            <a href="{{ $app->url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="app-card {{ $isFirst ? 'accent-card' : '' }}"
               style="{{ !$isFirst ? 'background:'.$bg.';' : '' }}"
               x-show="query === '' || '{{ addslashes(strtolower($app->name)) }}'.includes(query) || '{{ addslashes(strtolower($app->description ?? '')) }}'.includes(query)">

                {{-- Top Row --}}
                <div class="card-top">
                    <div class="card-title-block">
                        <div class="card-title">{{ $app->name }}</div>
                        <div class="card-domain">
                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z"/>
                            </svg>
                            {{ $domain }}
                            {{-- Verified badge --}}
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $accent }}" style="margin-left:2px">
                                <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Logo / Abbr --}}
                    @if($app->logo)
                        <img src="{{ Storage::url($app->logo) }}" alt="{{ $app->name }}" class="card-logo">
                    @else
                        <div class="card-logo-placeholder">{{ $abbr }}</div>
                    @endif
                </div>

                {{-- Description --}}
                <p class="card-desc">{{ $app->description ?? 'Sistem informasi ' . $app->name . ' BPS Kabupaten Demak.' }}</p>

                {{-- Footer --}}
                <div class="card-footer">
                    <div class="card-meta">
                        <div class="card-meta-item">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            SSO
                        </div>
                        <div class="card-meta-item">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            Online
                        </div>
                    </div>
                    <span class="btn-pill" onclick="event.preventDefault(); window.open('{{ $app->url }}', '_blank')">
                        Buka Aplikasi
                    </span>
                </div>
            </a>
            @empty
            <div style="grid-column:1/-1; text-align:center; padding: 48px 24px; color:#64748b; background:#fff; border-radius:20px; border:1.5px dashed #e2e8f0;">
                <p style="font-size:15px;">Belum ada aplikasi yang ditambahkan.</p>
            </div>
            @endforelse

        </div>
    </section>

    {{-- ─── FOOTER ─── --}}
    <footer>
        <strong>BPS Kabupaten Demak</strong> &mdash; Sipetra Portal &copy; {{ date('Y') }}
    </footer>

</body>
</html>
