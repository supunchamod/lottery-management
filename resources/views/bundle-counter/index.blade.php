<x-layouts.app title="Bundle Counter">

@push('head')
<style>
    /* SVG ring progress animation */
    .ring-progress {
        transition: stroke-dashoffset 0.35s ease;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    /* Pulse glow when bundle completes */
    @keyframes bundleComplete {
        0%   { box-shadow: 0 0 0 0 rgba(99,102,241,.7); }
        70%  { box-shadow: 0 0 0 28px rgba(99,102,241,0); }
        100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
    }
    .pulse-complete { animation: bundleComplete 0.9s ease-out; }

    /* Scan flash */
    @keyframes scanFlash {
        0%   { background-color: rgba(99,102,241,.25); }
        100% { background-color: transparent; }
    }
    .scan-flash { animation: scanFlash 0.4s ease-out; }

    /* Error shake */
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        20%,60% { transform: translateX(-6px); }
        40%,80% { transform: translateX(6px); }
    }
    .shake { animation: shake 0.35s ease; }
</style>
@endpush

<div
    x-data="bundleCounter()"
    x-init="init()"
    @keydown.window="onKey($event)"
    class="max-w-7xl mx-auto space-y-6"
>

    {{-- ══════════════════ PAGE HEADER ══════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Ticket Bundle Counter</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Scan barcodes to count tickets — every 100 completes a bundle
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="resetSession()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-800 px-3.5 py-2 text-sm font-medium
                           text-slate-600 dark:text-slate-300 hover:border-red-400 hover:text-red-500
                           dark:hover:border-red-500 dark:hover:text-red-400 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset
            </button>
            <button @click="saveSession()"
                    :disabled="totalTickets === 0 || saving"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold
                           text-white shadow-sm hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed
                           transition-colors">
                <svg x-show="!saving" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <svg x-show="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span x-text="saving ? 'Saving…' : 'Save Session'"></span>
            </button>
        </div>
    </div>

    {{-- ══════════════════ INVISIBLE SCANNER INPUT ══════════════════ --}}
    <input
        x-ref="scanInput"
        x-model="scanBuffer"
        @input.debounce.120ms="processScan()"
        type="text"
        autocomplete="off"
        class="fixed -top-full -left-full w-px h-px opacity-0 pointer-events-none"
        aria-label="Barcode scanner input"
    >

    {{-- ══════════════════ MAIN GRID ══════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- ─── Left column: progress ring + status ─── --}}
        <div class="lg:col-span-2 flex flex-col gap-4">

            {{-- Progress Ring Card --}}
            <div :class="completionFlash ? 'pulse-complete' : ''"
                 class="glass-card rounded-2xl border border-slate-200 dark:border-slate-700/60
                        bg-white dark:bg-slate-900 p-6 flex flex-col items-center text-center shadow-sm">

                {{-- SVG Ring --}}
                <div class="relative w-52 h-52 select-none">
                    <svg class="w-full h-full" viewBox="0 0 200 200">
                        {{-- Background track --}}
                        <circle cx="100" cy="100" r="85"
                                fill="none"
                                stroke="currentColor"
                                class="text-slate-200 dark:text-slate-700"
                                stroke-width="14"/>
                        {{-- Progress arc --}}
                        <circle
                            cx="100" cy="100" r="85"
                            fill="none"
                            :stroke="currentCount >= 100 ? '#10b981' : '#6366f1'"
                            stroke-width="14"
                            stroke-linecap="round"
                            class="ring-progress"
                            :stroke-dasharray="534"
                            :stroke-dashoffset="534 - (534 * (currentCount / 100))"
                        />
                    </svg>
                    {{-- Center number --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-6xl font-extrabold tabular-nums leading-none"
                              :class="currentCount >= 100
                                  ? 'text-emerald-500'
                                  : 'text-slate-900 dark:text-white'"
                              x-text="currentCount">
                        </span>
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-1 tracking-widest uppercase">
                            / 100
                        </span>
                    </div>
                </div>

                {{-- Label --}}
                <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-300">
                    Current Bundle Count
                </p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"
                   x-text="'Bundle #' + (completedBundles.length + 1) + ' in progress'">
                </p>

                {{-- Status indicator --}}
                <div class="mt-4 flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-semibold"
                     :class="scannerActive
                         ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                         : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                    <span class="h-2 w-2 rounded-full"
                          :class="scannerActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                    <span x-text="scannerActive ? 'Scanner Active' : 'Click anywhere to activate'"></span>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                            bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Completed Bundles
                    </p>
                    <p class="mt-1.5 text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums"
                       x-text="completedBundles.length"></p>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                            bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Total Tickets
                    </p>
                    <p class="mt-1.5 text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums"
                       x-text="totalTickets"></p>
                </div>
            </div>

            {{-- Last scan info --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                        bg-white dark:bg-slate-900 p-4 shadow-sm text-sm"
                 :class="lastScanError ? 'shake' : ''"
                 @animationend="lastScanError = false">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                    Last Scan
                </p>
                <template x-if="lastBarcode">
                    <div class="flex items-center justify-between gap-2">
                        <code class="font-mono text-sm text-slate-800 dark:text-slate-200 truncate"
                              x-text="lastBarcode"></code>
                        <span :class="lastScanError
                                  ? 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400'
                                  : 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400'"
                              class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                              x-text="lastScanError ? 'Duplicate!' : 'OK'">
                        </span>
                    </div>
                </template>
                <template x-if="!lastBarcode">
                    <p class="text-slate-400 dark:text-slate-600 italic">Waiting for scan…</p>
                </template>
            </div>

            {{-- Manual entry (for testing / keyboards) --}}
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700
                        bg-white dark:bg-slate-900 p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                    Manual Entry
                </p>
                <form @submit.prevent="manualScan()" class="flex gap-2">
                    <input x-model="manualInput" type="text" placeholder="Type barcode…"
                           class="erp-input flex-1 text-sm"/>
                    <button type="submit"
                            class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white
                                   hover:bg-indigo-500 transition-colors">
                        Add
                    </button>
                </form>
            </div>
        </div>

        {{-- ─── Right column: completed bundles table + session logs ─── --}}
        <div class="lg:col-span-3 flex flex-col gap-4">

            {{-- Completed bundles --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                        bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Completed Bundles</h3>
                    <span class="rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5
                                 text-xs font-bold text-indigo-700 dark:text-indigo-300"
                          x-text="completedBundles.length + ' bundle' + (completedBundles.length === 1 ? '' : 's')">
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800
                                       bg-slate-50/80 dark:bg-slate-800/50">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">#</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tickets</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Completed At</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="completedBundles.length === 0">
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400 dark:text-slate-600">
                                        No completed bundles yet. Start scanning!
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(bundle, i) in completedBundles" :key="i">
                                <tr class="border-b border-slate-50 dark:border-slate-800/60
                                           hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-5 py-3 font-bold text-slate-700 dark:text-slate-300"
                                        x-text="'#' + bundle.number"></td>
                                    <td class="px-5 py-3 font-semibold tabular-nums text-slate-800 dark:text-slate-200">
                                        <span x-text="bundle.count"></span>
                                        <span class="text-xs text-slate-400 ml-1">tickets</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs font-mono"
                                        x-text="bundle.time"></td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full
                                                     bg-emerald-50 dark:bg-emerald-500/10
                                                     px-2.5 py-0.5 text-xs font-semibold
                                                     text-emerald-700 dark:text-emerald-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Complete
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Partial bundle row --}}
                <template x-if="currentCount > 0">
                    <div class="px-5 py-3 bg-indigo-50/70 dark:bg-indigo-500/5
                                border-t border-indigo-100 dark:border-indigo-500/20
                                flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                            <svg class="h-4 w-4 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4l2 2"/>
                            </svg>
                            <span x-text="'Bundle #' + (completedBundles.length + 1) + ' — in progress'"></span>
                        </div>
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 tabular-nums"
                              x-text="currentCount + ' / 100'"></span>
                    </div>
                </template>
            </div>

            {{-- Total summary --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                        bg-gradient-to-br from-indigo-50 to-violet-50
                        dark:from-indigo-500/10 dark:to-violet-500/10
                        dark:border-indigo-500/20 p-5 shadow-sm">
                <div class="flex flex-wrap gap-5 items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-widest">
                            Session Summary
                        </p>
                        <p class="mt-1 text-3xl font-extrabold text-indigo-800 dark:text-indigo-200 tabular-nums"
                           x-text="totalTickets + ' Tickets'"></p>
                        <p class="text-sm text-indigo-600/70 dark:text-indigo-400/70 mt-0.5"
                           x-text="completedBundles.length + ' complete bundle' + (completedBundles.length===1?'':'s') + (currentCount > 0 ? ' + ' + currentCount + ' partial' : '')">
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-indigo-600/60 dark:text-indigo-400/60">Scanned barcodes</p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300 tabular-nums"
                           x-text="scannedBarcodes.length"></p>
                    </div>
                </div>

                {{-- Notes field (visible before save) --}}
                <div class="mt-4">
                    <label class="text-xs font-medium text-indigo-700 dark:text-indigo-300">
                        Notes (optional)
                    </label>
                    <textarea x-model="notes" rows="2" maxlength="500"
                              placeholder="e.g. Morning shift, Lottery Draw #1234…"
                              class="mt-1 w-full rounded-xl border border-indigo-200 dark:border-indigo-500/30
                                     bg-white/70 dark:bg-slate-900/60 px-3 py-2 text-sm
                                     text-slate-700 dark:text-slate-200 placeholder-slate-400
                                     focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none">
                    </textarea>
                </div>
            </div>

            {{-- ─── Saved Session Logs (server-rendered) ─── --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60
                        bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Saved Sessions</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Last 20 saved bundle sessions</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800
                                       bg-slate-50/80 dark:bg-slate-800/50">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bundles</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tickets</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saved By</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr class="border-b border-slate-50 dark:border-slate-800/60
                                       hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300">
                                    {{ $log->session_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 tabular-nums font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $log->total_bundles }}
                                </td>
                                <td class="px-5 py-3 tabular-nums font-semibold text-slate-800 dark:text-slate-200">
                                    {{ number_format($log->total_tickets) }}
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $log->savedBy?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-400 dark:text-slate-500 text-xs max-w-xs truncate">
                                    {{ $log->notes ?: '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-600">
                                    No saved sessions yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function bundleCounter() {
    return {
        // ── State ────────────────────────────────────────────────────────────
        currentCount:      0,
        completedBundles:  [],
        scannedBarcodes:   [],   // deduplicated list for this session
        scanBuffer:        '',
        manualInput:       '',
        lastBarcode:       '',
        lastScanError:     false,
        notes:             '',
        saving:            false,
        scannerActive:     false,
        completionFlash:   false,

        // ── Computed ─────────────────────────────────────────────────────────
        get totalTickets() {
            return this.completedBundles.reduce((s, b) => s + b.count, 0) + this.currentCount;
        },

        // ── Init ─────────────────────────────────────────────────────────────
        init() {
            // Click anywhere to re-focus scanner input
            document.addEventListener('click', () => {
                this.$refs.scanInput.focus();
                this.scannerActive = true;
            });
            this.$nextTick(() => {
                this.$refs.scanInput.focus();
                this.scannerActive = true;
            });
            // Unfocus detection
            this.$refs.scanInput.addEventListener('blur', () => {
                setTimeout(() => { this.scannerActive = false; }, 200);
            });
            this.$refs.scanInput.addEventListener('focus', () => {
                this.scannerActive = true;
            });
        },

        // ── Key passthrough to hidden input ──────────────────────────────────
        onKey(e) {
            // Let Enter from scanner submit the current buffer
            if (e.key === 'Enter' && document.activeElement !== this.$refs.scanInput) {
                if (this.scanBuffer.trim()) this.processScan();
            }
        },

        // ── Process a scan from the hidden input ─────────────────────────────
        processScan() {
            const code = this.scanBuffer.trim();
            this.scanBuffer = '';
            if (!code) return;
            this.handleBarcode(code);
            // Re-focus immediately after
            this.$nextTick(() => this.$refs.scanInput.focus());
        },

        // ── Manual scan via text box ─────────────────────────────────────────
        manualScan() {
            const code = this.manualInput.trim();
            this.manualInput = '';
            if (!code) return;
            this.handleBarcode(code);
        },

        // ── Core barcode handler ─────────────────────────────────────────────
        handleBarcode(code) {
            this.lastBarcode = code;

            // Duplicate check
            if (this.scannedBarcodes.includes(code)) {
                this.lastScanError = true;
                this.playError();
                this.speak('Duplicate ticket! Already scanned.');
                return;
            }

            // Register barcode
            this.scannedBarcodes.push(code);
            this.currentCount++;
            this.lastScanError = false;

            // Flash the card
            this.$nextTick(() => {
                const el = this.$el.querySelector('.scan-target');
                if (el) {
                    el.classList.remove('scan-flash');
                    void el.offsetWidth;  // reflow
                    el.classList.add('scan-flash');
                }
            });

            if (this.currentCount < 100) {
                // Speak the number
                this.speak(String(this.currentCount));
                this.playBeep();
            } else {
                // Bundle complete!
                const bundleNum = this.completedBundles.length + 1;
                this.completedBundles.push({
                    number: bundleNum,
                    count:  this.currentCount,
                    time:   new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                });
                this.currentCount = 0;

                // Celebratory flash
                this.completionFlash = true;
                setTimeout(() => { this.completionFlash = false; }, 1000);

                this.playSuccess();
                setTimeout(() => {
                    this.speak(`Bundle ${bundleNum} Completed! Starting Next Bundle.`);
                }, 300);
            }
        },

        // ── Save session to server ────────────────────────────────────────────
        async saveSession() {
            if (this.totalTickets === 0 || this.saving) return;
            this.saving = true;

            try {
                const res = await fetch('{{ route('bundle-counter.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        session_date:     new Date().toISOString().slice(0, 10),
                        total_bundles:    this.completedBundles.length,
                        total_tickets:    this.totalTickets,
                        scanned_barcodes: this.scannedBarcodes,
                        notes:            this.notes,
                    }),
                });

                if (res.ok) {
                    showToast('success', `Session saved — ${this.totalTickets} tickets in ${this.completedBundles.length} bundles.`);
                    this.speak('Session saved successfully.');
                    // Reload to refresh saved-sessions table
                    setTimeout(() => window.location.reload(), 1800);
                } else {
                    const body = await res.json().catch(() => ({}));
                    showToast('error', body.message || 'Save failed. Please try again.');
                }
            } catch (e) {
                showToast('error', 'Network error. Please try again.');
            } finally {
                this.saving = false;
            }
        },

        // ── Reset session (with confirmation) ────────────────────────────────
        resetSession() {
            if (this.totalTickets === 0) return;
            if (!confirm(`Reset session? You have ${this.totalTickets} tickets scanned. This cannot be undone.`)) return;
            this.currentCount     = 0;
            this.completedBundles = [];
            this.scannedBarcodes  = [];
            this.lastBarcode      = '';
            this.lastScanError    = false;
            this.notes            = '';
            showToast('info', 'Session reset.');
        },

        // ── Audio feedback ────────────────────────────────────────────────────
        playBeep() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.12);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.12);
            } catch(e) {}
        },

        playSuccess() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const notes = [523, 659, 784, 1047];
                notes.forEach((freq, i) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.1);
                    gain.gain.setValueAtTime(0.18, ctx.currentTime + i * 0.1);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + i * 0.1 + 0.18);
                    osc.start(ctx.currentTime + i * 0.1);
                    osc.stop(ctx.currentTime + i * 0.1 + 0.18);
                });
            } catch(e) {}
        },

        playError() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.35);
            } catch(e) {}
        },

        // ── Text-to-speech ────────────────────────────────────────────────────
        speak(text) {
            try {
                if (!window.speechSynthesis) return;
                window.speechSynthesis.cancel();
                const utt = new SpeechSynthesisUtterance(text);
                utt.lang  = 'en-US';
                utt.rate  = 1.1;
                utt.pitch = 1.0;
                utt.volume = 0.9;
                window.speechSynthesis.speak(utt);
            } catch(e) {}
        },
    };
}
</script>
@endpush

</x-layouts.app>
