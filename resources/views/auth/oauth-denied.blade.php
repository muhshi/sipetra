<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak — SIPETRA SSO</title>
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-danger: #ef4444;
            --border: #334155;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px;
            max-width: 520px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            position: relative;
        }

        .icon-container::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid rgba(239, 68, 68, 0.2);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        .icon {
            font-size: 32px;
            filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.5));
        }

        .badge {
            display: inline-block;
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 99px;
            margin-bottom: 24px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(to bottom, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .app-name {
            color: #fb923c;
            font-weight: 700;
            padding: 2px 4px;
            border-radius: 4px;
            background: rgba(251, 146, 60, 0.1);
        }

        .divider { 
            height: 1px; 
            background: linear-gradient(to right, transparent, var(--border), transparent); 
            margin: 32px 0; 
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px;
            background: rgba(15, 23, 42, 0.3);
            border-radius: 12px;
            margin-bottom: 32px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: white;
        }

        .user-details {
            text-align: left;
        }

        .user-name {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .user-email {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        .footer {
            margin-top: 48px;
            font-size: 12px;
            color: #475569;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-container">
            <div class="icon">🚫</div>
        </div>
        <span class="badge">Akses Ditolak</span>
        <h1>Izin Diperlukan</h1>
        <p>
            Akun Anda saat ini tidak memiliki izin untuk masuk ke sistem 
            <span class="app-name">{{ $clientName ?? 'Aplikasi Client' }}</span>.
        </p>
        <p style="font-size: 14px;">
            Hubungi Administrator Sipetra untuk memberikan akses ke aplikasi ini.
        </p>

        <div class="divider"></div>

        <div class="user-info">
            <div class="user-avatar">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="user-details">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-email">{{ auth()->user()->email }}</span>
            </div>
        </div>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-ghost">Kembali</a>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" style="border: none; cursor: pointer; font-family: inherit;">
                    Logout & Ganti Akun
                </button>
            </form>
        </div>

        <div class="footer">
            SIPETRA SSO &bull; Sistem Identitas Pegawai & Mitra
        </div>
    </div>
</body>
</html>
