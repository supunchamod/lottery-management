<x-layouts.app title="Manage Sub-Sellers">

<div class="max-w-3xl">

    {{-- Back link + assistant selector --}}
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('ticket-distribution.index', ['assistant_id' => $assistant->id]) }}"
           class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            ← Back to Distribution
        </a>

        <form method="GET" action="{{ route('ticket-distribution.sub-sellers.index', $assistant->id) }}">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Switch assistant:</label>
                <select name="redirect_assistant" onchange="
                    window.location='/ticket-distribution/'+this.value+'/sub-sellers'
                " class="erp-input text-sm h-8 pr-8">
                    @foreach($assistants as $a)
                        <option value="{{ $a->id }}" @selected($a->id == $assistant->id)>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Assistant card --}}
    <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-5 py-4">
        <p class="text-xs text-blue-500 font-medium uppercase tracking-wide mb-0.5">Sales Assistant</p>
        <p class="text-lg font-bold text-gray-900">{{ $assistant->name }}</p>
        <p class="text-sm text-gray-500">{{ $assistant->phone ?? 'No phone' }}</p>
    </div>

    {{-- Add new sub-seller form --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Add New Sub-Seller</h3>
        <form method="POST" action="{{ route('ticket-distribution.sub-sellers.store') }}"
              class="flex flex-wrap gap-3 items-end">
            @csrf
            <input type="hidden" name="assistant_id" value="{{ $assistant->id }}">

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Name *</label>
                <input type="text" name="name" required placeholder="Sub-seller name"
                       class="erp-input w-full text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                <input type="text" name="phone" placeholder="07X-XXXXXXX"
                       class="erp-input w-full text-sm">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add
            </button>
        </form>
    </div>

    {{-- Sub-sellers list --}}
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">
                Sub-Sellers
                <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                    {{ $subSellers->count() }}
                </span>
            </h3>
        </div>

        @if($subSellers->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400">
                No sub-sellers yet. Add the first one above.
            </div>
        @else
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Phone</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subSellers as $i => $seller)
                        <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400">{{ $i + 1 }}</td>

                            <td class="px-5 py-3">
                                {{-- View mode --}}
                                <span x-show="!editing" class="font-medium text-gray-800">
                                    {{ $seller->name }}
                                </span>
                                {{-- Edit mode --}}
                                <form x-show="editing" method="POST"
                                      action="{{ route('ticket-distribution.sub-sellers.update', $seller) }}"
                                      class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $seller->name }}"
                                           required class="erp-input text-sm w-40">
                                    <input type="text" name="phone" value="{{ $seller->phone }}"
                                           placeholder="Phone" class="erp-input text-sm w-32">
                                    <input type="hidden" name="is_active" value="{{ $seller->is_active ? 1 : 0 }}">
                                    <button type="submit"
                                            class="text-xs bg-emerald-600 text-white rounded px-2 py-1 hover:bg-emerald-700">
                                        Save
                                    </button>
                                    <button type="button" @click="editing=false"
                                            class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                </form>
                            </td>

                            <td class="px-5 py-3 text-gray-500" x-show="!editing">
                                {{ $seller->phone ?? '—' }}
                            </td>
                            <td class="px-5 py-3" x-show="!editing">
                                @if($seller->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Inactive</span>
                                @endif
                            </td>

                            <td class="px-5 py-3 text-right" x-show="!editing">
                                <div class="inline-flex gap-2">
                                    <button @click="editing=true"
                                            class="text-xs text-blue-600 hover:underline">Edit</button>
                                    <form method="POST"
                                          action="{{ route('ticket-distribution.sub-sellers.destroy', $seller) }}"
                                          onsubmit="return confirm('Deactivate {{ $seller->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:underline">
                                            {{ $seller->is_active ? 'Deactivate' : 'Already Inactive' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

</x-layouts.app>
