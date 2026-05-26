<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        try {
            $appSettings = [
                'store_name' => Setting::get('store_name', config('app.name', 'CamboMart')),
                'logo' => Setting::get('logo'),
                'currency' => Setting::get('currency', 'USD'),
                'address' => Setting::get('address'),
                'phone' => Setting::get('phone'),
                'email' => Setting::get('email'),
            ];
        } catch (\Exception $e) {
            $appSettings = [
                'store_name' => config('app.name', 'CamboMart'),
                'logo' => null,
                'currency' => 'USD',
                'address' => null,
                'phone' => null,
                'email' => null,
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'appSettings' => $appSettings,
        ];
    }
}
