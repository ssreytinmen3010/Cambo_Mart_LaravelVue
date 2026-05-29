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
            'address' => Setting::get('address'),
            'phone' => Setting::get('phone'),
            'map_lat' => Setting::get('map_lat'),
            'map_long' => Setting::get('map_long'),
            'bakong_account_id' => Setting::get('bakong_account_id', config('services.bakong.account_id')),
            'bakong_merchant_id' => Setting::get('bakong_merchant_id', config('services.bakong.merchant_id', '123456')),
            'bakong_merchant_name' => Setting::get('bakong_merchant_name', config('services.bakong.merchant_name', 'DAVIT YEM')),
            'bakong_merchant_city' => Setting::get('bakong_merchant_city', config('services.bakong.merchant_city', 'Phnom Penh')),
            'bakong_acquiring_bank' => Setting::get('bakong_acquiring_bank', config('services.bakong.acquiring_bank', 'Dev Bank')),
            'bakong_qr_timeout_minutes' => Setting::get('bakong_qr_timeout_minutes', config('services.bakong.qr_timeout_minutes', 10)),
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
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'map_lat' => 'required|string|max:50',
            'map_long' => 'required|string|max:50',
            'bakong_account_id' => 'nullable|string|max:255',
            'bakong_merchant_id' => 'nullable|string|max:255',
            'bakong_merchant_name' => 'nullable|string|max:255',
            'bakong_merchant_city' => 'nullable|string|max:255',
            'bakong_acquiring_bank' => 'nullable|string|max:255',
            'bakong_qr_timeout_minutes' => 'nullable|integer|min:1|max:1440',
        ]);

        Setting::set('store_name', $validated['store_name']);
        Setting::set('currency', $validated['currency']);
        Setting::set('address', $validated['address']);
        Setting::set('phone', $validated['phone']);
        Setting::set('map_lat', $validated['map_lat']);
        Setting::set('map_long', $validated['map_long']);
        Setting::set('bakong_account_id', $validated['bakong_account_id'] ?? null);
        Setting::set('bakong_merchant_id', $validated['bakong_merchant_id'] ?? null);
        Setting::set('bakong_merchant_name', $validated['bakong_merchant_name'] ?? null);
        Setting::set('bakong_merchant_city', $validated['bakong_merchant_city'] ?? null);
        Setting::set('bakong_acquiring_bank', $validated['bakong_acquiring_bank'] ?? null);
        Setting::set('bakong_qr_timeout_minutes', $validated['bakong_qr_timeout_minutes'] ?? null);

        if (isset($validated['logo'])) {
            Setting::set('logo', $validated['logo']);
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}
