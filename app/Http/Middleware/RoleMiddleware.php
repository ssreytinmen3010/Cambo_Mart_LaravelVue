<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->role->name !== $role) {
            // Redirect unauthorized users to their appropriate dashboard
            if (Auth::user()->role->name === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->role->name === 'user') {
                return redirect()->route('user.dashboard');
            } else {
                // Fallback for unknown roles
                return redirect('/');
            }
        }

        return $next($request);
    }
}
