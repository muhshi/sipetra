<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Panduan Integrasi Cepat (Laravel Client)</h3>
    </div>

    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
        <div>
            <p class="font-medium text-gray-800 dark:text-gray-200 mb-1">1. Tambahkan ke file <code class="px-1.5 py-0.5 rounded bg-gray-100 text-pink-600 font-mono text-xs dark:bg-gray-800">.env</code> aplikasi klien Anda:</p>
            <div class="relative rounded-lg bg-slate-900 p-3 font-mono text-xs text-sky-400 overflow-x-auto">
                <pre>SIPETRA_CLIENT_ID="{{ $record->id }}"
SIPETRA_CLIENT_SECRET="{{ $record->plain_secret }}"
SIPETRA_REDIRECT_URI="{{ is_array($record->redirect_uris) ? ($record->redirect_uris[0] ?? '') : '' }}"
SIPETRA_BASE_URL="{{ config('app.url') }}"</pre>
            </div>
        </div>

        <div>
            <p class="font-medium text-gray-800 dark:text-gray-200 mb-1">2. Konfigurasi <code class="px-1.5 py-0.5 rounded bg-gray-100 text-pink-600 font-mono text-xs dark:bg-gray-800">config/services.php</code> di aplikasi klien:</p>
            <div class="rounded-lg bg-slate-900 p-3 font-mono text-xs text-emerald-400 overflow-x-auto">
                <pre>'sipetra' => [
    'client_id' => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect' => env('SIPETRA_REDIRECT_URI'),
    'base_url' => env('SIPETRA_BASE_URL', '{{ config('app.url') }}'),
],</pre>
            </div>
        </div>

        <div>
            <p class="font-medium text-gray-800 dark:text-gray-200 mb-1">3. Endpoint Autentikasi SIPETRA:</p>
            <ul class="list-disc pl-5 space-y-1 text-xs">
                <li><strong>Authorize URL:</strong> <code class="font-mono text-blue-600 dark:text-blue-400">{{ config('app.url') }}/oauth/authorize</code></li>
                <li><strong>Token URL:</strong> <code class="font-mono text-blue-600 dark:text-blue-400">{{ config('app.url') }}/oauth/token</code></li>
                <li><strong>User Profile API:</strong> <code class="font-mono text-blue-600 dark:text-blue-400">{{ config('app.url') }}/api/user</code> (Bearer Token)</li>
                <li><strong>Single Logout (SLO):</strong> <code class="font-mono text-blue-600 dark:text-blue-400">{{ config('app.url') }}/oauth/logout?redirect_uri=...</code></li>
            </ul>
        </div>

        <div>
            <p class="font-medium text-gray-800 dark:text-gray-200 mb-1">4. Implementasi Single Logout (SLO) di Aplikasi Klien:</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Agar sesi SIPETRA ikut terputus saat user logout dari aplikasi Anda (mencegah login otomatis tanpa password):</p>
            <div class="rounded-lg bg-slate-900 p-3 font-mono text-xs text-amber-300 overflow-x-auto">
                <pre>// Pada Controller Logout aplikasi klien Anda:
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

$returnUrl = urlencode(route('login'));
return redirect(config('services.sipetra.base_url') . '/oauth/logout?redirect_uri=' . $returnUrl);</pre>
            </div>
        </div>
    </div>
</div>
