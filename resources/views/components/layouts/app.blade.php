<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'W.R Soysa' }} — Lottery ERP</title>

    {{-- ── Dark-mode FOUC prevention (runs sync before first paint) ─────────── --}}
    <script>
        try {
            const t = localStorage.getItem('theme')
                   ?? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (t === 'dark') document.documentElement.classList.add('dark');
        } catch(e){}
    </script>

    {{-- ── Google Fonts: Plus Jakarta Sans + Inter ───────────────────────────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ── Alpine.js (deferred) ───────────────────────────────────────────────── --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ── Chart.js ────────────────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

{{-- ── Body: Alpine shell drives dark-mode, sidebar collapse & mobile menu ─── --}}
<body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased transition-colors duration-200"
      x-data="appShell()">

{{-- ════════════════════ MOBILE OVERLAY ════════════════════════════════════ --}}
<div x-show="mobileOpen"
     x-cloak
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden"
     @click="mobileOpen = false">
</div>

{{-- ════════════════════ SIDEBAR ═══════════════════════════════════════════ --}}
<aside class="sidebar-glass fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out"
       :class="[
           collapsed ? 'w-[4.25rem]' : 'w-64',
           mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
       ]">

    {{-- Brand header --}}
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/5 px-[0.95rem]">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                    bg-gradient-to-br from-indigo-500 to-indigo-700 shadow-lg shadow-indigo-900/40">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1 overflow-hidden"
             x-show="!collapsed"
             x-transition:enter="transition ease-out duration-150 delay-75"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <p class="text-sm font-bold text-white leading-tight truncate">W.R Soysa</p>
            <p class="text-xs text-slate-500 truncate">NLB · DLB Lottery Agent</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-4 space-y-0.5">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('dashboard') ? 'nav-item-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
           :title="collapsed ? 'Dashboard' : ''">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-white transition-colors' }}"
                 fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span x-show="!collapsed" class="truncate">Dashboard</span>
        </a>

        {{-- Lottery Dropdown --}}
        <div x-data="{ open: {{ request()->routeIs('lotteries.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(collapsed) { collapsed = false }"
                    class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('lotteries.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                    :title="collapsed ? 'Lottery' : ''">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('lotteries.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-white transition-colors' }}"
                         fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">Lottery</span>
                </div>
                <svg x-show="!collapsed"
                     :class="open ? 'rotate-180' : ''"
                     class="h-4 w-4 shrink-0 transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open && !collapsed" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="ml-8 mt-0.5 space-y-0.5 border-l border-white/5 pl-3">
                <a href="{{ route('lotteries.index') }}"
                   class="block rounded-lg px-2 py-2 text-xs transition-colors
                          {{ request()->routeIs('lotteries.index') ? 'text-indigo-400 font-semibold bg-white/5' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                    Lottery List
                </a>
                <a href="{{ route('lotteries.create') }}"
                   class="block rounded-lg px-2 py-2 text-xs transition-colors
                          {{ request()->routeIs('lotteries.create') ? 'text-indigo-400 font-semibold bg-white/5' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                    Create Lottery
                </a>
            </div>
        </div>

        @php
            $isAdmin  = auth()->check() && auth()->user()->isAdmin();
            $navItems = [
                ['route' => 'stock.index',               'label' => 'Stock',            'adminOnly' => false, 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['route' => 'bundle-counter.index',      'label' => 'Bundle Counter',   'adminOnly' => false, 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                
                ['route' => 'assistants.index',          'label' => 'Assistants',       'adminOnly' => false, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'daily-sales.index',         'label' => 'Daily Sales',      'adminOnly' => false, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['route' => 'ticket-distribution.index', 'label' => 'Ticket Dist.',    'adminOnly' => false, 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                ['route' => 'board-settlement.index',   'label' => 'Board Settlement', 'adminOnly' => false, 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z'],
                ['route' => 'board-transactions.index', 'label' => 'Board Ledger',     'adminOnly' => false, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12l2 2 4-4'],
                ['route' => 'expenses.index',           'label' => 'Expenses',         'adminOnly' => false, 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['route' => 'cheques.index',            'label' => 'Cheques',          'adminOnly' => false, 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['route' => 'reports.index',            'label' => 'Reports',          'adminOnly' => true,  'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'activity-logs.index',      'label' => 'Activity Log',     'adminOnly' => true,  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['route' => 'users.index',              'label' => 'User Management',  'adminOnly' => true,  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ];
        @endphp

        @foreach($navItems as $item)
            @if($item['adminOnly'] && !$isAdmin) @continue @endif
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ $active ? 'nav-item-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
               :title="collapsed ? '{{ addslashes($item['label']) }}' : ''">
                <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-white transition-colors' }}"
                     fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span x-show="!collapsed" class="flex-1 truncate">{{ $item['label'] }}</span>
                @if($item['adminOnly'])
                    <span x-show="!collapsed"
                          class="shrink-0 rounded-full bg-indigo-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-400">
                        Admin
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Sidebar collapse toggle (desktop only) --}}
    <div class="hidden lg:block border-t border-white/5 p-2">
        <button @click="collapsed = !collapsed"
                class="flex w-full items-center justify-center rounded-xl p-2.5 text-slate-600 hover:bg-white/5 hover:text-white transition-colors"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
            <svg class="h-4 w-4 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- User profile + logout --}}
    @auth
    <div class="border-t border-white/5 px-3 py-3">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                        bg-gradient-to-br from-indigo-500 to-purple-600 text-xs font-bold text-white shadow-md">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div x-show="!collapsed" class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs truncate {{ auth()->user()->isAdmin() ? 'text-indigo-400' : 'text-slate-500' }}">
                    {{ auth()->user()->isAdmin() ? 'Administrator' : 'Sub-admin' }}
                </p>
            </div>
            <form x-show="!collapsed" method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" title="Sign out"
                        class="rounded-lg p-1.5 text-slate-500 hover:text-red-400 hover:bg-white/5 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
        <p x-show="!collapsed" class="mt-2 text-[11px] text-slate-600 truncate">
            Girandurukotte &amp; Mahiyanganya
        </p>
    </div>
    @endauth
</aside>

{{-- ════════════════════ MAIN CONTENT AREA ════════════════════════════════ --}}
<div class="flex flex-1 flex-col min-h-screen transition-all duration-300 ease-in-out"
     :class="collapsed ? 'lg:ml-[4.25rem]' : 'lg:ml-64'">

    {{-- ── Top header bar ────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between gap-4
                   border-b border-slate-200/80 dark:border-slate-800/60
                   bg-white/85 dark:bg-slate-900/85 backdrop-blur-xl
                   px-4 sm:px-6">

        {{-- Left: mobile hamburger + page title --}}
        <div class="flex items-center gap-3 min-w-0">
            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="btn-action lg:hidden rounded-xl p-2 text-slate-500 dark:text-slate-400
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="min-w-0">
                <h1 class="text-base font-bold text-slate-900 dark:text-white leading-tight truncate">
                    {{ $title ?? 'Dashboard' }}
                </h1>
                <p class="hidden sm:block text-xs text-slate-400 dark:text-slate-500">
                    {{ now()->format('l, d F Y') }}
                </p>
            </div>
        </div>

        {{-- Right: date badge · dark toggle · avatar --}}
        <div class="flex items-center gap-2 shrink-0">
            {{-- Date badge --}}
            <span class="hidden sm:inline-flex items-center rounded-full
                         bg-indigo-50 dark:bg-indigo-500/10
                         border border-indigo-100 dark:border-indigo-500/20
                         px-3 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                {{ now()->format('d M Y') }}
            </span>

            {{-- Dark mode toggle --}}
            <button @click="dark = !dark"
                    class="btn-action rounded-xl p-2 text-slate-500 dark:text-slate-400
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    :title="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                {{-- Sun icon (shown in dark mode) --}}
                <svg x-show="dark" class="h-5 w-5 text-amber-400"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{-- Moon icon (shown in light mode) --}}
                <svg x-show="!dark" class="h-5 w-5"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- User avatar --}}
            @auth
            <div class="flex h-8 w-8 items-center justify-center rounded-full
                        bg-gradient-to-br from-indigo-500 to-purple-600
                        text-xs font-bold text-white shadow-md"
                 title="{{ auth()->user()->name }}">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            @endauth
        </div>
    </header>

    {{-- ── Toast notification container ────────────────────────────────────── --}}
    <div id="toast-container"
         class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2 pointer-events-none"
         style="max-width:22rem;"></div>

    {{-- ── Page content ──────────────────────────────────────────────────────── --}}
    <main class="flex-1 p-4 sm:p-6">
        {{ $slot }}
    </main>
</div>

{{-- ═══════════════════════════ SCRIPTS ════════════════════════════════════ --}}
@stack('scripts')

<script>
// ── App shell Alpine component ────────────────────────────────────────────────
function appShell() {
    return {
        dark:       false,
        collapsed:  false,
        mobileOpen: false,

        init() {
            // Set up reactivity watchers FIRST (so they fire when we set values)
            this.$watch('dark', v => {
                document.documentElement.classList.toggle('dark', v);
                localStorage.setItem('theme', v ? 'dark' : 'light');
            });
            this.$watch('collapsed', v => {
                localStorage.setItem('sidebar', v ? 'collapsed' : 'expanded');
            });

            // Read stored preferences
            const storedTheme = localStorage.getItem('theme');
            this.dark = storedTheme === 'dark'
                     || (!storedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            this.collapsed = localStorage.getItem('sidebar') === 'collapsed';

            // Fire flash-message toasts
            @if(session('success'))
                showToast('success', @json(session('success')));
            @endif
            @if(session('error'))
                showToast('error', @json(session('error')));
            @endif
        },
    };
}

// ── Hot-toast style notification system ──────────────────────────────────────
function showToast(type, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const cfg = {
        success: { dot: 'bg-emerald-500', icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>', ring: 'ring-emerald-500/20' },
        error:   { dot: 'bg-red-500',     icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>', ring: 'ring-red-500/20' },
        info:    { dot: 'bg-blue-500',     icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/>', ring: 'ring-blue-500/20' },
    };
    const c = cfg[type] || cfg.info;

    const toast = document.createElement('div');
    toast.className = [
        'pointer-events-auto flex items-center gap-3 rounded-2xl px-4 py-3.5',
        'bg-slate-900/96 backdrop-blur-xl',
        'shadow-2xl shadow-black/30',
        'ring-1', c.ring,
        'toast-enter',
    ].join(' ');
    toast.style.minWidth = '17rem';

    toast.innerHTML = `
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full ${c.dot} shadow-sm">
            <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                ${c.icon}
            </svg>
        </div>
        <p class="flex-1 text-sm font-medium text-white leading-snug">${message}</p>
        <button class="shrink-0 rounded-lg p-1 text-slate-500 hover:text-white transition-colors"
                onclick="const t=this.closest('.pointer-events-auto');t.classList.replace('toast-enter','toast-leave');setTimeout(()=>t.remove(),260)">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    container.appendChild(toast);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        toast.classList.replace('toast-enter', 'toast-leave');
        setTimeout(() => toast.remove(), 280);
    }, 5000);
}
</script>
</body>
</html>
