<x-layouts.app title="User Management">

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">User Management</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage Admin and Sub-admin accounts</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 transition-colors shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        @php
            $adminCount    = $users->where('role', 'admin')->count();
            $subAdminCount = $users->where('role', 'sub-admin')->count();
        @endphp
        <div class="rounded-xl bg-slate-800 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-70">Total Users</p>
            <p class="text-3xl font-bold mt-1">{{ $users->count() }}</p>
        </div>
        <div class="rounded-xl bg-blue-600 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-80">Admins</p>
            <p class="text-3xl font-bold mt-1">{{ $adminCount }}</p>
        </div>
        <div class="rounded-xl bg-indigo-500 text-white px-5 py-4 shadow-sm">
            <p class="text-xs font-medium opacity-80">Sub-admins</p>
            <p class="text-3xl font-bold mt-1">{{ $subAdminCount }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-slate-200 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">User</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left w-28">Role</th>
                        <th class="px-5 py-3 text-left w-36">Joined</th>
                        <th class="px-5 py-3 text-right w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        {{-- User --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full text-white text-xs font-bold shrink-0
                                            {{ $user->role === 'admin' ? 'bg-blue-600' : 'bg-indigo-500' }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="ml-1 text-xs text-gray-400 font-normal">(you)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $user->email }}</td>

                        {{-- Role badge --}}
                        <td class="px-5 py-3">
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-2.5 py-0.5">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold px-2.5 py-0.5">
                                    Sub-admin
                                </span>
                            @endif
                        </td>

                        {{-- Joined --}}
                        <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-700 px-3 py-1.5 text-xs font-medium transition-colors">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg border border-red-200 bg-white hover:bg-red-50 text-red-500 hover:text-red-700 px-3 py-1.5 text-xs font-medium transition-colors">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-layouts.app>
