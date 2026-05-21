<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::latest()->get();
        return Inertia::render('Admin/Location/Index', [
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'map_lat' => 'nullable|numeric',
            'map_long' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        Location::create($validated);

        return redirect()->back()->with('success', 'Location created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'map_lat' => 'nullable|numeric',
            'map_long' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        $location->update($validated);

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}
