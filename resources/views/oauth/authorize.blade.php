<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otorisasi Aplikasi — SIPETRA SSO</title>
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
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .logo-circle {
            width: 64px;
            height: 64px;
            background: #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
        }
        .arrow { color: #475569; font-size: 24px; }
        .app-logo {
            width: 64px;
            height: 64px;
            background: #f97316;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 12px;
        }
        p {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .app-name { color: #f97316; font-weight: 700; }
        
        .scopes-list {
            text-align: left;
            background: #0f172a;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #1e293b;
        }
        .scopes-list h2 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 12px;
        }
        .scope-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .scope-item svg { width: 16px; height: 16px; color: #22c55e; margin-right: 8px; }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            width: 100%;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover { background: #1d4ed8; }
        
        .btn-secondary {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
        }
        .btn-secondary:hover { background: #334155; color: white; }

        .footer {
            margin-top: 32px;
            font-size: 11px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-container">
            <div class="logo-circle">S</div>
            <div class="arrow">→</div>
            <div class="app-logo">{{ substr($client->name, 0, 1) }}</div>
        </div>

        <h1>Otorisasi Aplikasi</h1>
        <p>
            Aplikasi <span class="app-name">{{ $client->name }}</span> meminta izin untuk mengakses akun SIPETRA Anda.
        </p>

        @if (count($scopes) > 0)
            <div class="scopes-list">
                <h2>Izin yang diminta:</h2>
                @foreach ($scopes as $scope)
                    <div class="scope-item">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ $scope->description }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="actions">
            <!-- Form Otorisasi Passport -->
            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-primary">Izinkan Akses</button>
            </form>

            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('delete')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-secondary">Batalkan</button>
            </form>
        </div>

        <div class="footer">
            Login sebagai: <strong style="color: #cbd5e1">{{ $user->name }}</strong>
        </div>
    </div>
</body>
</html>
