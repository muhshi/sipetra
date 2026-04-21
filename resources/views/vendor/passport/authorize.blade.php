<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Persetujuan Akses - SIPETRA BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col items-center pt-10 sm:pt-16">
    <div class="w-full sm:max-w-xl px-6">
        
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('logoBpsDemakHitam.png') }}" alt="Logo BPS" class="h-14 w-auto mb-4 object-contain">
            <h2 class="text-2xl font-bold text-gray-800 text-center">Permintaan Akses</h2>
            <p class="text-sm text-gray-500 mt-1">Single Sign-On SIPETRA</p>
        </div>

        <div class="bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-gray-100">
            <div class="px-6 py-8 sm:p-10">
                <div class="flex items-center space-x-5 mb-8">
                    <!-- Client App Icon -->
                    <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $client->name }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $client->redirect }}</p>
                    </div>
                </div>

                <p class="text-base text-gray-700 leading-relaxed mb-6">
                    Aplikasi <strong>{{ $client->name }}</strong> meminta izin untuk mengakses akun Anda di SIPETRA BPS (<strong>{{ Auth::user()->email ?? Auth::user()->name }}</strong>).
                </p>

                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 mb-8">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Izin Akses yang Diminta:</h4>
                    
                    @if (count($scopes) > 0)
                        <ul class="space-y-4">
                            @foreach ($scopes as $scope)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0 h-5 w-5 mt-0.5 relative">
                                        <div class="absolute inset-0 bg-blue-100 rounded-full"></div>
                                        <svg class="absolute inset-0 h-5 w-5 text-blue-600 p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $scope->id }}</p>
                                        <p class="text-sm text-gray-500">{{ $scope->description }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Aplikasi ini hanya akan mengakses identitas dasar Anda.
                        </p>
                    @endif
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4">
                    <!-- Tombol Batal -->
                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="mt-3 sm:mt-0">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                            Batalkan
                        </button>
                    </form>

                    <!-- Tombol Setuju -->
                    <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                        @csrf
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 font-medium text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Izinkan Akses
                        </button>
                    </form>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                <span class="text-xs text-gray-500">Anda login sebagai <strong>{{ Auth::user()->name }}</strong></span>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:underline">Ganti Akun</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
