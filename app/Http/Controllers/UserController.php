<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'role'                  => ['required', 'in:admin,sub-admin'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index')
            ->with('success', "User '{$data['name']}' created successfully.");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'  => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'  => ['required', 'in:admin,sub-admin'],
        ];

        // Only validate password if provided
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)->letters()->numbers()];
        }

        $data = $request->validate($rules);

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

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
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
