<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Check if user is active
        if ($user->status !== 'Active') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact administrator.',
            ]);
        }

        if ($user->role->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role->name === 'user') {
            return redirect()->route('user.dashboard');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }


protected function authenticated(Request $request, $user)
{
    // Check role and redirect
    if ($user->role->name === 'admin') {
        return redirect()->route('admin.dashboard');
    }
   
    if ($user->role->name === 'user') {
        return redirect()->route('user.dashboard');
    }

    // fallback
    return redirect()->route('dashboard');
}

}
