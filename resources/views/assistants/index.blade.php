<x-layouts.app title="Sales Assistants">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Sales Assistants</h2>
            <p class="text-sm text-gray-500">Manage your field sales agents and their balances.</p>
        </div>
        <a href="{{ route('assistants.create') }}"
           class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Assistant
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($assistants ?? [] as $a)
            <div class="stat-card rounded-xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                            {{ strtoupper(substr($a->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $a->name }}</p>
                            <p class="text-xs text-gray-400">{{ $a->phone }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold
                                 {{ $a->current_balance > 0 ? 'bg-red-100 text-red-600' : ($a->current_balance < 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-600') }}">
                        {{ $a->current_balance > 0 ? 'Owes' : ($a->current_balance < 0 ? 'Credit' : 'Settled') }}
                    </span>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                    <span class="text-xs text-gray-400">Current Balance</span>
                    <span class="text-base font-bold {{ $a->current_balance > 0 ? 'text-red-600' : ($a->current_balance < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                        Rs.{{ number_format(abs($a->current_balance), 2) }}
                    </span>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('assistants.ledger', $a) }}"
                       class="flex-1 rounded-md border border-blue-200 py-1.5 text-center text-xs font-medium text-blue-600 hover:bg-blue-50">
                        Ledger
                    </a>
                    <a href="{{ route('assistants.edit', $a) }}"
                       class="flex-1 rounded-md border border-gray-200 py-1.5 text-center text-xs font-medium text-gray-600 hover:bg-gray-50">
                        Edit
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-12 text-center text-sm text-gray-400">
                No assistants registered yet.
                <a href="{{ route('assistants.create') }}" class="text-blue-600 hover:underline">Add one →</a>
            </div>
        @endforelse
    </div>

</x-layouts.app>
