<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user || !$user->role || $user->role->name !== 'user') {
            return redirect('/login');
        }
        return view('user.dashboard', compact('user'));
    }
}

