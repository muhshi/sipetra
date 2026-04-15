<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-3xl items-center px-6 py-12">
        <div class="w-full rounded-3xl border border-slate-200 bg-white p-10 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-600">OAuth Access Denied</p>
            <h1 class="mt-4 text-3xl font-bold">Aplikasi ini belum mengizinkan akun Anda.</h1>
            <p class="mt-4 text-base text-slate-600">
                {{ $user->name }} tidak memiliki rule akses yang cocok untuk client
                <span class="font-semibold text-slate-900">{{ $client->name }}</span>.
            </p>
            <p class="mt-2 text-sm text-slate-500">
                Silakan hubungi admin SIPETRA atau pemilik aplikasi untuk meminta akses.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">
                    Kembali ke Dashboard
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
