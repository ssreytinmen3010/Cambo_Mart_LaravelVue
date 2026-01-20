<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user || !$user->role || $user->role->name !== 'admin') {
            return redirect('/login');
        }
        return view('admin.dashboard', compact('user'));
    }
}
