<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak — SIPETRA SSO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 48px 56px;
            max-width: 480px;
            width: 90%;
            text-align: center;
        }
        .icon {
            width: 72px;
            height: 72px;
            background: #450a0a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
        }
        .badge {
            display: inline-block;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 99px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 12px;
        }
        p {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 8px;
        }
        .app-name {
            color: #f97316;
            font-weight: 600;
        }
        .divider { height: 1px; background: #334155; margin: 28px 0; }
        .contact-note {
            font-size: 13px;
            color: #64748b;
        }
        .btn {
            display: inline-block;
            margin-top: 28px;
            padding: 10px 28px;
            background: #1e40af;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover { background: #1d4ed8; }
        .sipetra-logo {
            margin-top: 32px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔒</div>
        <span class="badge">Akses Ditolak</span>
        <h1>Anda Tidak Memiliki Akses</h1>
        <p>
            Akun Anda belum terdaftar sebagai pengguna yang diizinkan untuk mengakses
            <span class="app-name">{{ $client->name ?? 'aplikasi ini' }}</span>.
        </p>
        <p>
            Jika Anda merasa ini adalah kesalahan, hubungi administrator sistem untuk mendaftarkan akun Anda.
        </p>
        <div class="divider"></div>
        <p class="contact-note">
            Login sebagai: <strong style="color: #cbd5e1">{{ auth()->user()?->name ?? '—' }}</strong>
        </p>
        <a href="{{ url('/') }}" class="btn">← Kembali ke Beranda</a>
        <div class="sipetra-logo">SIPETRA — Sistem Identitas Pegawai &amp; Mitra</div>
    </div>
</body>
</html>
