<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('label')->get();
        return view('users.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'role'                  => ['required', 'in:admin,sub-admin'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'permissions'           => ['array'],
            'permissions.*'         => ['integer', 'exists:permissions,id'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'role'     => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            $this->syncPermissions($user, $data['permissions'] ?? []);

            return $user;
        });

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' created successfully.");
    }

    public function edit(User $user)
    {
        $permissions = Permission::orderBy('label')->get();
        $activePermissionIds = $user->permissions()->wherePivot('is_active', true)->pluck('permissions.id')->all();

        return view('users.edit', compact('user', 'permissions', 'activePermissionIds'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'          => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'          => ['required', 'in:admin,sub-admin'],
            'permissions'   => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        // Only validate password if provided
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)->letters()->numbers()];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($request, $user, $data) {
            $user->name  = $data['name'];
            $user->email = $data['email'];

            // Prevent changing own role
            if ($user->id !== auth()->id()) {
                $user->role = $data['role'];
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

            $this->syncPermissions($user, $data['permissions'] ?? []);
        });

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Sync the user's feature permissions. Admins bypass permission checks
     * entirely (see User::hasFeature), so their pivot rows are cleared;
     * sub-admins get a row per permission with is_active reflecting the
     * checkboxes submitted from the form.
     */
    private function syncPermissions(User $user, array $activePermissionIds): void
    {
        if ($user->role !== 'sub-admin') {
            $user->permissions()->detach();
            return;
        }

        $activePermissionIds = array_map('intval', $activePermissionIds);

        $syncData = Permission::pluck('id')->mapWithKeys(fn (int $id) => [
            $id => ['is_active' => in_array($id, $activePermissionIds, true)],
        ])->all();

        $user->permissions()->sync($syncData);
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User '{$name}' deleted.");
    }
}
