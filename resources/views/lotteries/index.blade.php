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

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="min-w-full text-sm divide-y divide-gray-100">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Board</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Unit Price</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
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
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('lotteries.edit', $l) }}"
                               class="text-xs text-blue-600 hover:underline">Edit</a>

                            <form action="{{ route('lotteries.destroy', $l) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete {{ addslashes($l->name) }}? This will remove all associated ticket data.');"
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg border border-red-200 px-2 py-1 text-red-600
                                               hover:bg-red-50 transition-colors"
                                        title="Delete">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-12 text-center text-sm text-gray-400">No lotteries added yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
