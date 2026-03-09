<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /** Show the login form. */
    public function create()
    {
        return view('auth.login');
    }

    /** Handle an incoming authentication request. */
    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // ── Rate limiting: max 5 attempts per minute per email + IP ────────────
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // ── Attempt authentication ─────────────────────────────────────────────
        if (! Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // ── Log the successful login ───────────────────────────────────────────
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'user_name'   => Auth::user()->name,
            'action'      => 'logged_in',
            'module'      => 'Auth',
            'description' => 'Logged in to the system',
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /** Destroy an authenticated session. */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
