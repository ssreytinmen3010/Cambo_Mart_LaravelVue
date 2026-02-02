<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $settings = [
            'store_name' => Setting::get('store_name', config('app.name', 'CamboMart')),
            'currency' => Setting::get('currency', 'USD'),
            'logo' => Setting::get('logo'),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'auth_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'image' => $user->image ?? null,
            ],
            'settings' => $settings,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|string',
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'image' => $validated['image'] ?? $user->image,
        ])->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'currency' => 'required|string|in:USD',
            'logo' => 'nullable|string',
        ]);

        Setting::set('store_name', $validated['store_name']);
        Setting::set('currency', $validated['currency']);
        if (isset($validated['logo'])) {
            Setting::set('logo', $validated['logo']);
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}