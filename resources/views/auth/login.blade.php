<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — W.R Soysa Lottery ERP</title>

    {{-- Google Fonts: Plus Jakarta Sans + Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Subtle animated gradient orbs */
        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            opacity: 0.25;
            animation: orb-drift 12s ease-in-out infinite alternate;
        }
        @keyframes orb-drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, -30px) scale(1.08); }
        }
    </style>
</head>

<body class="h-full font-sans antialiased" style="background-color: #060d1f;">

<div class="flex h-full">

    {{-- ══════════════════ LEFT BRANDING PANEL ══════════════════════════════ --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 relative overflow-hidden"
         style="background: linear-gradient(145deg, #060d1f 0%, #0f1e3d 55%, #1a2f6b 100%);">

        {{-- Background orbs --}}
        <div class="orb h-64 w-64 bg-indigo-600" style="top:-60px; left:-40px;"></div>
        <div class="orb h-48 w-48 bg-violet-600" style="bottom:80px; right:-20px; animation-delay:-4s;"></div>
        <div class="orb h-32 w-32 bg-blue-500"   style="bottom:200px; left:100px; animation-delay:-8s;"></div>

        {{-- Brand logo --}}
        <div class="relative flex items-center gap-3 z-10">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl
                        bg-gradient-to-br from-indigo-500 to-indigo-700
                        shadow-2xl shadow-indigo-900/60 ring-1 ring-white/10">
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

        {{-- Hero text --}}
        <div class="relative space-y-6 z-10">
            <div class="inline-flex items-center gap-2 rounded-full
                        border border-indigo-500/30 bg-indigo-500/10
                        px-3.5 py-1.5">
                <div class="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse"></div>
                <span class="text-xs text-indigo-300 font-medium">Lottery Management System</span>
            </div>
            <h1 class="text-4xl font-extrabold text-white leading-tight tracking-tight">
                Complete control<br>
                <span class="bg-gradient-to-r from-indigo-300 to-violet-300 bg-clip-text text-transparent">
                    of your agency.
                </span>
            </h1>
            <p class="text-slate-400 text-base leading-relaxed max-w-sm">
                Manage daily sales, board settlements, assistant performance,
                and financial reports — all in one place.
            </p>
        </div>

        {{-- Feature bullets --}}
        <div class="relative space-y-3 z-10">
            @foreach([
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Daily Sales & Board Settlement Tracking'],
                ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Sales Assistant & Sub-Seller Management'],
                ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Advanced Reports & Audit Trails'],
            ] as $feature)
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/8 ring-1 ring-white/10">
                    <svg class="h-4 w-4 text-indigo-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-300">{{ $feature['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════ RIGHT LOGIN PANEL ══════════════════════════════════ --}}
    <div class="flex flex-1 flex-col items-center justify-center px-6 py-12"
         style="background: #080f25;">

        {{-- Mobile logo --}}
        <div class="flex lg:hidden items-center gap-3 mb-10">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl
                        bg-gradient-to-br from-indigo-500 to-indigo-700
                        shadow-lg ring-1 ring-indigo-400/20">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-lg leading-tight">W.R Soysa</p>
                <p class="text-slate-400 text-xs">NLB · DLB Lottery ERP</p>
            </div>
        </div>

        <div class="w-full max-w-sm" x-data="{ showPassword: false }">

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Welcome back</h2>
                <p class="text-sm text-slate-400 mt-1.5">Sign in to access your ERP dashboard</p>
            </div>

            {{-- Session error --}}
            @if(session('error'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <svg class="h-4 w-4 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
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
                    <label for="email" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">
                        Email address
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}" required autofocus autocomplete="email"
                               style="background:#0e1a35; color:#f1f5f9;"
                               class="block w-full rounded-xl border pl-10 pr-4 py-3 text-sm
                                      placeholder-slate-600 transition-all duration-150
                                      focus:outline-none focus:ring-2
                                      {{ $errors->has('email')
                                            ? 'border-red-500/50 focus:ring-red-500/30'
                                            : 'border-slate-700/80 hover:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/25' }}"
                               placeholder="admin@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">
                        Password
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password"
                               :type="showPassword ? 'text' : 'password'"
                               name="password" required autocomplete="current-password"
                               style="background:#0e1a35; color:#f1f5f9;"
                               class="block w-full rounded-xl border pl-10 pr-11 py-3 text-sm
                                      placeholder-slate-600 transition-all duration-150
                                      focus:outline-none focus:ring-2
                                      {{ $errors->has('password')
                                            ? 'border-red-500/50 focus:ring-red-500/30'
                                            : 'border-slate-700/80 hover:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/25' }}"
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 transition-colors">
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
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" name="remember"
                               class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-indigo-600
                                      focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer">
                        <span class="text-sm text-slate-400 group-hover:text-slate-300 transition-colors">
                            Remember me for 30 days
                        </span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="btn-action w-full rounded-xl py-3 text-sm font-bold text-white
                               bg-gradient-to-r from-indigo-600 to-indigo-700
                               hover:from-indigo-500 hover:to-indigo-600
                               shadow-lg shadow-indigo-900/40
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900
                               transition-all duration-150">
                    Sign In to ERP
                </button>
            </form>

            {{-- Footer --}}
            <p class="mt-8 text-center text-xs text-slate-600">
                W.R Soysa Lottery Agency &mdash; Girandurukotte &amp; Mahiyanganya<br>
                072-0673295 / 078-4766684
            </p>
        </div>
    </div>
</div>

</body>
</html>
