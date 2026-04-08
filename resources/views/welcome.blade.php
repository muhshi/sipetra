<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPETRA — Sistem Single Sign-On</title>
    <meta name="description" content="SIPETRA adalah platform SSO terpusat untuk manajemen autentikasi dan otorisasi pengguna secara aman dan efisien.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --accent: #22d3ee;
            --bg-deep: #060714;
            --bg-dark: #0d0f1e;
            --bg-card: #111327;
            --bg-card-border: #1e2240;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #475569;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── BACKGROUND ── */
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .bg-glow-1 {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, transparent 70%);
            top: -200px;
            right: -100px;
            z-index: 0;
            pointer-events: none;
            animation: float1 12s ease-in-out infinite;
        }

        .bg-glow-2 {
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.1) 0%, transparent 70%);
            bottom: -200px;
            left: -100px;
            z-index: 0;
            pointer-events: none;
            animation: float2 14s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, 30px); }
        }

        @keyframes float2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, -20px); }
        }

        /* ── LAYOUT ── */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 1;
        }

        /* ── NAVBAR ── */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 0 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(6, 7, 20, 0.7);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }

        .nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .nav-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .nav-login-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid rgba(99, 102, 241, 0.4);
            box-shadow: 0 0 16px rgba(99, 102, 241, 0.25);
        }

        .nav-login-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            box-shadow: 0 0 24px rgba(99, 102, 241, 0.45);
            transform: translateY(-1px);
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 64px;
        }

        .hero-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 80px 0 60px;
            gap: 0;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 100px;
            font-size: 13px;
            font-weight: 500;
            color: var(--primary-light);
            margin-bottom: 32px;
            animation: fadeInDown 0.6s ease both;
        }

        .hero-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }

        .hero-title {
            font-size: clamp(40px, 7vw, 78px);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -2px;
            margin-bottom: 24px;
            animation: fadeInDown 0.6s ease 0.1s both;
        }

        .hero-title-plain { color: var(--text-primary); }

        .hero-title-gradient {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: clamp(15px, 2vw, 18px);
            color: var(--text-secondary);
            max-width: 560px;
            line-height: 1.7;
            margin-bottom: 44px;
            font-weight: 400;
            animation: fadeInDown 0.6s ease 0.2s both;
        }

        .hero-cta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeInDown 0.6s ease 0.3s both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.25s ease;
            border: 1px solid rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 28px rgba(99, 102, 241, 0.35), 0 4px 16px rgba(0,0,0,0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.55), 0 4px 20px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }

        .btn-primary svg {
            transition: transform 0.25s ease;
        }

        .btn-primary:hover svg {
            transform: translateX(3px);
        }

        /* ── DIVIDER ── */
        .section-divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.3), transparent);
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* ── FEATURES ── */
        .features {
            padding: 96px 0;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--primary-light);
            margin-bottom: 16px;
        }

        .section-title {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-primary);
            margin-bottom: 16px;
        }

        .section-desc {
            font-size: 16px;
            color: var(--text-secondary);
            max-width: 520px;
            line-height: 1.7;
        }

        .features-header {
            margin-bottom: 64px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--bg-card-border);
            border-radius: 16px;
            padding: 32px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.06), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            border-color: rgba(99, 102, 241, 0.35);
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(99,102,241,0.1);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            position: relative;
        }

        .icon-purple { background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.25); }
        .icon-cyan   { background: rgba(34, 211, 238, 0.12); border: 1px solid rgba(34, 211, 238, 0.2); }
        .icon-green  { background: rgba(52, 211, 153, 0.12); border: 1px solid rgba(52, 211, 153, 0.2); }
        .icon-orange { background: rgba(251, 146, 60, 0.12); border: 1px solid rgba(251, 146, 60, 0.2); }
        .icon-pink   { background: rgba(244, 114, 182, 0.12); border: 1px solid rgba(244, 114, 182, 0.2); }
        .icon-yellow { background: rgba(250, 204, 21, 0.12); border: 1px solid rgba(250, 204, 21, 0.2); }

        .feature-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .feature-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        /* ── STATS ── */
        .stats {
            padding: 72px 0;
        }

        .stats-inner {
            background: var(--bg-card);
            border: 1px solid var(--bg-card-border);
            border-radius: 20px;
            padding: 56px 48px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 40px;
            position: relative;
            overflow: hidden;
        }

        .stats-inner::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), var(--accent), transparent);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: -2px;
            background: linear-gradient(135deg, var(--text-primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ── CTA BOTTOM ── */
        .cta-bottom {
            padding: 96px 0;
        }

        .cta-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(34, 211, 238, 0.06));
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 24px;
            padding: 72px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-box::before {
            content: '';
            position: absolute;
            top: -80px; left: 50%; transform: translateX(-50%);
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15), transparent 70%);
            pointer-events: none;
        }

        .cta-box-title {
            font-size: clamp(26px, 4vw, 40px);
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 16px;
            position: relative;
        }

        .cta-box-desc {
            font-size: 16px;
            color: var(--text-secondary);
            max-width: 460px;
            margin: 0 auto 36px;
            line-height: 1.7;
            position: relative;
        }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--bg-card-border);
            padding: 28px 24px;
            position: relative;
            z-index: 1;
        }

        .footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-text {
            font-size: 13px;
            color: var(--text-muted);
        }

        .footer-brand {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-brand span {
            color: var(--primary-light);
            font-weight: 600;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease both;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .hero-inner { padding: 60px 0 40px; }
            .features-grid { grid-template-columns: 1fr; }
            .stats-inner { padding: 40px 28px; grid-template-columns: repeat(2, 1fr); }
            .cta-box { padding: 48px 24px; }
            .footer-inner { flex-direction: column; text-align: center; }
        }

        @media (max-width: 480px) {
            .stats-inner { grid-template-columns: 1fr 1fr; }
        }

        /* ── TAG PILL ── */
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.12);
            color: var(--primary-light);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="bg-grid"></div>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <!-- Navbar -->
    <nav>
        <div class="nav-inner">
            <a href="#" class="nav-logo">
                <div class="nav-logo-icon">S</div>
                <span class="nav-logo-text">SIPETRA</span>
            </a>
            <a href="{{ route('filament.admin.auth.login') }}" class="nav-login-btn" id="nav-login-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h6v18h-6M10 17l5-5-5-5M13.8 12H3"/>
                </svg>
                Login Admin
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Single Sign-On Platform
                </div>

                <h1 class="hero-title">
                    <span class="hero-title-plain">Satu Akun,<br></span>
                    <span class="hero-title-gradient">Semua Akses.</span>
                </h1>

                <p class="hero-desc">
                    SIPETRA adalah platform SSO terpusat yang mengkonsolidasikan autentikasi dan otorisasi pengguna lintas aplikasi secara aman, cepat, dan terstandarisasi dengan OAuth 2.0.
                </p>

                <div class="hero-cta">
                    <a href="{{ route('filament.admin.auth.login') }}" class="btn-primary" id="hero-login-btn">
                        Masuk sebagai Admin
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-header">
                <div class="section-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Fitur Unggulan
                </div>
                <h2 class="section-title">Dibangun untuk Kehandalan</h2>
                <p class="section-desc">Infrastruktur identitas yang solid untuk mendukung ekosistem aplikasi organisasi Anda.</p>
            </div>

            <div class="features-grid">

                <div class="feature-card animate-in" style="animation-delay: 0s;">
                    <div class="feature-icon icon-purple">🔐</div>
                    <div class="feature-title">OAuth 2.0 Standard</div>
                    <p class="feature-desc">Implementasi penuh protokol OAuth 2.0 dengan dukungan Authorization Code, Client Credentials, dan Personal Access Token.</p>
                </div>

                <div class="feature-card animate-in" style="animation-delay: 0.07s;">
                    <div class="feature-icon icon-cyan">⚡</div>
                    <div class="feature-title">Token Management</div>
                    <p class="feature-desc">Pengelolaan token akses dan refresh token dengan kebijakan kedaluwarsa yang dapat dikonfigurasi secara fleksibel.</p>
                </div>

                <div class="feature-card animate-in" style="animation-delay: 0.14s;">
                    <div class="feature-icon icon-green">🛡️</div>
                    <div class="feature-title">Scope-based Authorization</div>
                    <p class="feature-desc">Kontrol granular melalui sistem scope — batasi akses data berdasarkan hak yang diberikan per klien aplikasi.</p>
                </div>

                <div class="feature-card animate-in" style="animation-delay: 0.21s;">
                    <div class="feature-icon icon-orange">👥</div>
                    <div class="feature-title">Manajemen Pengguna</div>
                    <p class="feature-desc">Panel admin terpusat untuk mengelola pengguna, peran, satuan kerja, dan data organisasi secara lengkap.</p>
                </div>

                <div class="feature-card animate-in" style="animation-delay: 0.28s;">
                    <div class="feature-icon icon-pink">🔗</div>
                    <div class="feature-title">Multi-Client Support</div>
                    <p class="feature-desc">Daftarkan dan kelola banyak aplikasi klien dalam satu platform SSO yang terpadu dan mudah dioperasikan.</p>
                </div>

                <div class="feature-card animate-in" style="animation-delay: 0.35s;">
                    <div class="feature-icon icon-yellow">📊</div>
                    <div class="feature-title">Audit &amp; Monitoring</div>
                    <p class="feature-desc">Rekam jejak autentikasi dan aktivitas token untuk kebutuhan audit keamanan dan pemantauan sistem.</p>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-inner">
                <div class="stat-item">
                    <div class="stat-number">OAuth2</div>
                    <div class="stat-label">Protokol Standar</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">6</div>
                    <div class="stat-label">Scope Tersedia</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">JWT</div>
                    <div class="stat-label">Format Token</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">SSL</div>
                    <div class="stat-label">Enkripsi Penuh</div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- CTA Section -->
    <section class="cta-bottom">
        <div class="container">
            <div class="cta-box">
                <div class="pill" style="margin-bottom: 20px;">Panel Administrasi</div>
                <h2 class="cta-box-title">
                    Kelola Platform <span class="hero-title-gradient">dengan Mudah</span>
                </h2>
                <p class="cta-box-desc">
                    Akses panel administrasi SIPETRA untuk mengelola pengguna, klien OAuth, dan konfigurasi sistem secara terpusat.
                </p>
                <a href="{{ route('filament.admin.auth.login') }}" class="btn-primary" id="cta-login-btn" style="position: relative;">
                    Masuk ke Admin Panel
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <span class="footer-text">© {{ date('Y') }} SIPETRA. Seluruh hak dilindungi.</span>
            <div class="footer-brand">
                Dibangun dengan <span>Laravel</span> &amp; <span>Filament</span>
            </div>
        </div>
    </footer>

    <script>
        // Scroll-triggered animation for feature cards
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-in').forEach((el) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = `opacity 0.55s ease ${el.style.animationDelay}, transform 0.55s ease ${el.style.animationDelay}`;
            observer.observe(el);
        });
    </script>

</body>
</html>
