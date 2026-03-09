<x-layouts.app title="Activity Log">

@push('head')
<style>
    .badge-created  { @apply inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-2.5 py-0.5; }
    .badge-updated  { @apply inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold px-2.5 py-0.5; }
    .badge-deleted  { @apply inline-flex items-center gap-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-2.5 py-0.5; }
    .badge-login    { @apply inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-2.5 py-0.5; }
    .badge-other    { @apply inline-flex items-center gap-1 rounded-full bg-gray-100 border border-gray-200 text-gray-600 text-xs font-semibold px-2.5 py-0.5; }
    .module-pill    { @apply inline-block rounded-md bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5; }
</style>
@endpush

<div class="space-y-5">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Activity Log</h2>
            <p class="text-sm text-gray-500 mt-0.5">Full audit trail of every action in the system</p>
        </div>
    </div>

    {{-- ── Today's summary cards ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $cards = [
                ['label' => 'Total Today',  'value' => $todayCounts->total   ?? 0, 'color' => 'bg-slate-800',   'text' => 'text-white'],
                ['label' => 'Created',      'value' => $todayCounts->created ?? 0, 'color' => 'bg-emerald-500', 'text' => 'text-white'],
                ['label' => 'Updated',      'value' => $todayCounts->updated ?? 0, 'color' => 'bg-amber-400',   'text' => 'text-white'],
                ['label' => 'Deleted',      'value' => $todayCounts->deleted ?? 0, 'color' => 'bg-red-500',     'text' => 'text-white'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="rounded-xl {{ $card['color'] }} {{ $card['text'] }} px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-80">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Filter card ──────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap gap-3 items-end">

            {{-- Search --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="User, module, description…"
                       class="erp-input w-full">
            </div>

            {{-- Action --}}
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
                <select name="action" class="erp-input w-full">
                    <option value="">All Actions</option>
                    <option value="created"   {{ request('action') === 'created'   ? 'selected' : '' }}>Created</option>
                    <option value="updated"   {{ request('action') === 'updated'   ? 'selected' : '' }}>Updated</option>
                    <option value="deleted"   {{ request('action') === 'deleted'   ? 'selected' : '' }}>Deleted</option>
                    <option value="logged_in" {{ request('action') === 'logged_in' ? 'selected' : '' }}>Logged In</option>
                </select>
            </div>

            {{-- Module --}}
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Module</label>
                <select name="module" class="erp-input w-full">
                    <option value="">All Modules</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>
                            {{ $mod }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- User --}}
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <select name="user_id" class="erp-input w-full">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="erp-input w-full">
            </div>

            {{-- Date To --}}
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="erp-input w-full">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search','action','module','user_id','date_from','date_to']))
                <a href="{{ route('activity-logs.index') }}"
                   class="rounded-lg border border-gray-300 text-gray-600 text-sm font-medium px-4 py-2 hover:bg-gray-50 transition-colors">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        {{-- Result count --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
            <p class="text-xs text-gray-500">
                Showing <span class="font-semibold text-gray-700">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span>
                of <span class="font-semibold text-gray-700">{{ number_format($logs->total()) }}</span> entries
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-slate-200 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Time</th>
                        <th class="px-4 py-3 text-left w-36">User</th>
                        <th class="px-4 py-3 text-left w-24">Action</th>
                        <th class="px-4 py-3 text-left w-36">Module</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left w-28">IP Address</th>
                        <th class="px-4 py-3 text-center w-16">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" x-data>
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors group">

                        {{-- Time --}}
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            <div class="font-medium text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                            <div>{{ $log->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- User --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($log->user_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-800">{{ $log->user_name }}</p>
                                    @if($log->user)
                                        <p class="text-xs text-gray-400 capitalize">{{ $log->user->role }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Action badge --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="{{ $log->actionBadgeClass() }}">
                                @if($log->action === 'created')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                @elseif($log->action === 'updated')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                @elseif($log->action === 'deleted')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                @else
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14"/>
                                    </svg>
                                @endif
                                {{ $log->actionLabel() }}
                            </span>
                        </td>

                        {{-- Module --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="module-pill">{{ $log->module }}</span>
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-3 text-gray-700 text-xs max-w-xs">
                            {{ $log->description }}
                        </td>

                        {{-- IP --}}
                        <td class="px-4 py-3 text-gray-400 text-xs font-mono whitespace-nowrap">
                            {{ $log->ip_address ?? '—' }}
                        </td>

                        {{-- Detail toggle --}}
                        <td class="px-4 py-3 text-center">
                            @if($log->old_values || $log->new_values)
                            <button
                                @click="$dispatch('open-log-detail', { id: {{ $log->id }}, old: {{ json_encode($log->old_values) }}, new: {{ json_encode($log->new_values) }}, desc: {{ json_encode($log->description) }} })"
                                class="rounded-md border border-gray-200 bg-white hover:bg-gray-50 text-gray-400 hover:text-gray-600 p-1.5 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg class="h-10 w-10 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm font-medium">No activity logs found</p>
                                <p class="text-xs">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Detail modal ─────────────────────────────────────────────────────────── --}}
<div x-data="{
    open: false,
    id: null,
    desc: '',
    old: null,
    new: null,
    init() {
        window.addEventListener('open-log-detail', e => {
            this.id   = e.detail.id;
            this.desc = e.detail.desc;
            this.old  = e.detail.old;
            this.new  = e.detail.new;
            this.open = true;
        });
    }
}"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     @keydown.escape.window="open = false">

    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden"
         @click.outside="open = false">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Change Detail</h3>
                <p class="text-xs text-gray-500 mt-0.5" x-text="desc"></p>
            </div>
            <button @click="open = false" class="rounded-lg p-1.5 hover:bg-gray-200 transition-colors text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">

            {{-- Old values --}}
            <template x-if="old && Object.keys(old).length">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-2">Before</p>
                    <div class="rounded-lg border border-red-100 bg-red-50 overflow-hidden">
                        <table class="w-full text-xs">
                            <template x-for="[key, val] in Object.entries(old)" :key="key">
                                <tr class="border-b border-red-100 last:border-0">
                                    <td class="px-3 py-2 font-medium text-red-700 w-1/3" x-text="key.replaceAll('_',' ')"></td>
                                    <td class="px-3 py-2 text-red-900 font-mono" x-text="val ?? '(null)'"></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </template>

            {{-- New values --}}
            <template x-if="new && Object.keys(new).length">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-2">After</p>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 overflow-hidden">
                        <table class="w-full text-xs">
                            <template x-for="[key, val] in Object.entries(new)" :key="key">
                                <tr class="border-b border-emerald-100 last:border-0">
                                    <td class="px-3 py-2 font-medium text-emerald-700 w-1/3" x-text="key.replaceAll('_',' ')"></td>
                                    <td class="px-3 py-2 text-emerald-900 font-mono" x-text="val ?? '(null)'"></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="(!old || !Object.keys(old).length) && (!new || !Object.keys(new).length)">
                <p class="text-sm text-gray-400 text-center py-4">No field-level detail recorded for this entry.</p>
            </template>
        </div>
    </div>
</div>

</x-layouts.app>
