<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    
        public function list() {
            $users = \App\Models\User::with('role')->get();
            return response()->json($users);
        }
    
        public function updateStatus(Request $request, $userId) {
            $request->validate([
                'status' => 'required|in:Active,Inactive'
            ]);
    
            $user = \App\Models\User::findOrFail($userId);
            $user->status = $request->status;
            $user->save();
    
            return response()->json(['message' => 'User status updated successfully']);
        }
    
        public function store(Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:50',
                'password' => 'required|string|min:6',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:Active,Inactive'
            ]);

            // Check phone uniqueness manually if phone is provided
            if (!empty($validated['phone'])) {
                $existingUser = \App\Models\User::where('phone', $validated['phone'])->first();
                if ($existingUser) {
                    return response()->json([
                        'message' => 'The phone number has already been taken.',
                        'errors' => ['phone' => ['The phone number has already been taken.']]
                    ], 422);
                }
            }

            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null, // Ensure null if empty
                'password' => bcrypt($validated['password']),
                'role_id' => $validated['role_id'],
                'status' => $validated['status']
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('role')
            ]);
        }
    
        public function update(Request $request, $userId) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
                'phone' => 'nullable|string|max:50|unique:users,phone,' . $userId,
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:Active,Inactive'
            ]);

            $user = \App\Models\User::findOrFail($userId);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'status' => $request->status
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->load('role')
            ]);
        }

}

