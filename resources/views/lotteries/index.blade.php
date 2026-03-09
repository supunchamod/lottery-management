<x-layouts.app title="Lotteries">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Lotteries</h2>
        <p class="text-sm text-gray-500">Manage NLB & DLB lottery products.</p>
    </div>
    <a href="{{ route('lotteries.create') }}"
       class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Lottery
    </a>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="min-w-full text-sm divide-y divide-gray-100">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Board</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Unit Price</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Commission %</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($lotteries as $l)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $l->name }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                     {{ $l->board === 'NLB' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $l->board }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-700">Rs. {{ number_format($l->unit_price, 2) }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($l->commission_rate, 2) }}%</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('lotteries.edit', $l) }}"
                           class="text-xs text-blue-600 hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-12 text-center text-sm text-gray-400">No lotteries added yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
