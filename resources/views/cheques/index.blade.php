<x-layouts.app title="Cheques">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Cheque Management</h2>
            <p class="text-sm text-gray-500">Track pending and cleared cheques.</p>
        </div>
        <a href="{{ route('cheques.create') }}"
           class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Cheque
        </a>
    </div>

    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Pending Cheques</p>
            <p class="text-xl font-bold text-amber-600">Rs.{{ number_format($pendingTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Cleared This Month</p>
            <p class="text-xl font-bold text-emerald-600">Rs.{{ number_format($clearedTotal ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-400">Overdue</p>
            <p class="text-xl font-bold text-red-600">{{ $overdueCount ?? 0 }} cheques</p>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bank</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cheque No.</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Due Date</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($cheques ?? [] as $c)
                        @php $overdue = $c->isPending() && $c->due_date->isPast(); @endphp
                        <tr class="{{ $overdue ? 'bg-red-50/40' : '' }}">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $c->bank_name }}</td>
                            <td class="px-5 py-3 font-mono text-gray-600">{{ $c->cheque_no }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-800">Rs.{{ number_format($c->amount, 2) }}</td>
                            <td class="px-5 py-3 {{ $overdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ $c->due_date->format('d M Y') }}
                                @if($overdue)<span class="ml-1 text-xs text-red-500">Overdue</span>@endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold
                                             {{ $c->isCleared() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($c->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($c->isPending())
                                    <form method="POST" action="{{ route('cheques.clear', $c) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-blue-600 hover:underline">Mark Cleared</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">No cheques recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
