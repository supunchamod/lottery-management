<x-layouts.app title="Sales Assistants">

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Sales Assistants</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage your field sales agents and their balances.</p>
        </div>
        <a href="{{ route('assistants.create') }}"
           class="btn-action flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700
                  px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-900/20 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Sales Assistant
        </a>
    </div>

    @if($assistants->isEmpty())
        <div class="col-span-3 py-16 text-center">
            <div class="mx-auto h-20 w-20 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <svg class="h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No assistants registered yet.</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                <a href="{{ route('assistants.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Add your first assistant →</a>
            </p>
        </div>
    @else
        {{-- ── Alpine search scope ──────────────────────────────────────────── --}}
        <div x-data="{
                search: '',
                q()         { return this.search.toLowerCase().trim(); },
                cardMatch(name, phone) {
                    const q = this.q();
                    return !q || name.includes(q) || phone.includes(q);
                },
                groupMatch(cards) {
                    const q = this.q();
                    return !q || cards.some(c => c.name.includes(q) || c.phone.includes(q));
                },
                hasAnyMatch(cards) {
                    const q = this.q();
                    return !q || cards.some(c => c.name.includes(q) || c.phone.includes(q));
                }
             }">

            {{-- ── Search bar ────────────────────────────────────────────────── --}}
            <div class="mb-5 flex items-center gap-2">
                <div class="relative w-72">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                    <input type="text"
                           x-model="search"
                           placeholder="Search Assistant by Name or Phone..."
                           autocomplete="off"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-800
                                  pl-9 pr-8 py-2 text-sm
                                  text-slate-700 dark:text-slate-300
                                  placeholder-slate-400 dark:placeholder-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-500/40 focus:border-indigo-300">
                    <button x-show="search"
                            @click="search = ''"
                            type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            title="Clear search">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <span x-show="search.trim()"
                      class="text-xs text-slate-400 dark:text-slate-500"
                      x-text="hasAnyMatch({{ json_encode(
                          $assistants->map(fn($a) => [
                              'name'  => mb_strtolower($a->name),
                              'phone' => mb_strtolower($a->phone ?? ''),
                          ])->values()->toArray()
                      ) }}) ? '' : 'No results'"></span>
            </div>

            {{-- ── Grouped assistant cards ────────────────────────────────────── --}}
            @foreach($grouped as $routeName => $group)
                @php
                    $groupCards = $group->map(fn($a) => [
                        'name'  => mb_strtolower($a->name),
                        'phone' => mb_strtolower($a->phone ?? ''),
                    ])->values()->toArray();
                @endphp

                <div class="mb-2 mt-4 first:mt-0"
                     x-show="groupMatch({{ json_encode($groupCards) }})">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3 px-1">
                        {{ $routeName }}
                        <span class="ml-1 rounded-full bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-slate-500 dark:text-slate-400 normal-case tracking-normal">
                            {{ $group->count() }}
                        </span>
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($group as $a)
                            @php
                                $hasSales = $a->daily_sales_count > 0;
                                if ($a->current_balance > 0)      { $statusLabel = 'Credit';    $statusClass = 'bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400'; }
                                elseif ($a->current_balance < 0)  { $statusLabel = 'Excess';    $statusClass = 'bg-blue-100 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400'; }
                                elseif ($hasSales)                 { $statusLabel = 'Settled';   $statusClass = 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'; }
                                else                               { $statusLabel = 'Not Sales'; $statusClass = 'bg-slate-100 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400'; }
                            @endphp

                            <div class="stat-card rounded-2xl
                                        bg-white dark:bg-slate-800/60
                                        border border-slate-100 dark:border-slate-700/50
                                        shadow-sm dark:shadow-slate-900/30 p-5
                                        hover:shadow-md dark:hover:shadow-slate-900/50"
                                 x-show="cardMatch('{{ mb_strtolower(addslashes($a->name)) }}', '{{ mb_strtolower(addslashes($a->phone ?? '')) }}')">

                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full
                                                    bg-gradient-to-br from-indigo-500 to-violet-600
                                                    text-white font-bold text-sm shadow-md">
                                            {{ strtoupper(substr($a->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-white">{{ $a->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $a->phone }}</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                {{-- Balance bar --}}
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-700/40 pt-3">
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Current Balance</span>
                                    <span class="text-base font-bold
                                                 {{ $a->current_balance > 0
                                                        ? 'text-red-600 dark:text-red-400'
                                                        : ($a->current_balance < 0
                                                            ? 'text-blue-600 dark:text-blue-400'
                                                            : 'text-slate-500 dark:text-slate-400') }}">
                                        Rs.{{ number_format(abs($a->current_balance), 2) }}
                                    </span>
                                </div>

                                {{-- Action buttons --}}
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('assistants.ledger', $a) }}"
                                    class="btn-action flex-1 rounded-xl border border-indigo-200 dark:border-indigo-500/30
                                            py-2 text-center text-xs font-semibold
                                            text-indigo-600 dark:text-indigo-400
                                            hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors">
                                        Ledger
                                    </a>
                                    <a href="{{ route('assistants.edit', $a) }}"
                                    class="btn-action flex-1 rounded-xl border border-slate-200 dark:border-slate-600/50
                                            py-2 text-center text-xs font-semibold
                                            text-slate-600 dark:text-slate-400
                                            hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        Edit
                                    </a>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('assistants.destroy', $a) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this assistant? This action cannot be undone.');"
                                        class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-xl border border-red-200 dark:border-red-500/30
                                                    px-3 py-2 text-center text-xs font-semibold
                                                    text-red-600 dark:text-red-400
                                                    hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- ── No search match ───────────────────────────────────────────── --}}
            <div x-show="search.trim() && !hasAnyMatch({{ json_encode(
                    $assistants->map(fn($a) => [
                        'name'  => mb_strtolower($a->name),
                        'phone' => mb_strtolower($a->phone ?? ''),
                    ])->values()->toArray()
                ) }})"
                 class="py-16 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-slate-300 dark:text-slate-600"
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    No assistant matching
                    "<span x-text="search" class="text-slate-700 dark:text-slate-300"></span>"
                </p>
                <button @click="search = ''"
                        class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    Clear search
                </button>
            </div>

        </div>{{-- /x-data --}}
    @endif

</x-layouts.app>
