<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — W.R Soysa Lottery ERP</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-900 font-sans antialiased">

<div class="flex h-full">

    {{-- ══════════════════════ LEFT BRANDING PANEL ══════════════════════ --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12"
         style="background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #1d4ed8 100%);">

        {{-- Logo --}}
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-lg leading-tight">W.R Soysa</p>
                <p class="text-slate-400 text-xs">NLB · DLB Lottery Agent</p>
            </div>
        </div>

        {{-- Center text --}}
        <div class="space-y-6">
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1.5">
                <div class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></div>
                <span class="text-xs text-blue-300 font-medium">Lottery Management System</span>
            </div>
            <h1 class="text-4xl font-bold text-white leading-tight">
                Complete control<br>
                <span class="text-blue-400">of your agency.</span>
            </h1>
            <p class="text-slate-400 text-base leading-relaxed max-w-sm">
                Manage daily sales, board settlements, assistant performance,
                and financial reports — all in one place.
            </p>
        </div>

        {{-- Feature bullets --}}
        <div class="space-y-3">
            @foreach([
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Daily Sales & Board Settlement Tracking'],
                ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Sales Assistant & Sub-Seller Management'],
                ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Advanced Reports & Audit Trails'],
            ] as $feature)
            <div class="flex items-center gap-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/10">
                    <svg class="h-4 w-4 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-300">{{ $feature['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════ RIGHT LOGIN PANEL ═════════════════════════ --}}
    <div class="flex flex-1 flex-col items-center justify-center px-8 py-12 bg-white">

        {{-- Mobile logo --}}
        <div class="flex lg:hidden items-center gap-3 mb-10">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            <p class="text-gray-900 font-bold text-lg">W.R Soysa Lottery ERP</p>
        </div>

        <div class="w-full max-w-sm" x-data="{ showPassword: false }">

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                <p class="text-sm text-gray-500 mt-1">Sign in to your account to continue</p>
            </div>

            {{-- Session error --}}
            @if(session('error'))
            <div class="mb-5 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            {{-- Login form --}}
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}" required autofocus autocomplete="email"
                               class="block w-full rounded-xl border pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400
                                      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-300 bg-white hover:border-gray-400' }}"
                               placeholder="admin@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password"
                               :type="showPassword ? 'text' : 'password'"
                               name="password" required autocomplete="current-password"
                               class="block w-full rounded-xl border pl-10 pr-10 py-3 text-sm text-gray-900 placeholder-gray-400
                                      transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-300 bg-white hover:border-gray-400' }}"
                               placeholder="••••••••">
                        {{-- Toggle visibility --}}
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm text-gray-600">Remember me for 30 days</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                               text-white text-sm font-semibold py-3 transition-colors shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Sign In to ERP
                </button>
            </form>

            {{-- Footer note --}}
            <p class="mt-8 text-center text-xs text-gray-400">
                W.R Soysa Lottery Agency &mdash; Girandurukotte &amp; Mahiyanganya<br>
                072-0673295 / 078-4766684
            </p>
        </div>
    </div>
</div>

</body>
</html>
