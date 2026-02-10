<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PlanSetting;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::orderBy('name')->paginate(20);

        return view('management.locations-index', [
            'locations' => $locations,
        ]);
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('management.locations-create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = PlanSetting::current();
        if ($plan && $plan->max_locations) {
            $currentCount = Location::count();
            if ($currentCount >= $plan->max_locations) {
                return back()->withErrors([
                    'limit' => 'Location limit reached for the current plan.',
                ])->withInput();
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'geo_lat' => ['nullable', 'numeric'],
            'geo_lng' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
            'product_ids' => ['array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $location = Location::create($data);
        $location->products()->sync($data['product_ids'] ?? []);

        return redirect()->route('management.locations.index')->with('status', 'Location created.');
    }

    public function edit(Location $location): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $location->load('products');

        return view('management.locations-edit', [
            'location' => $location,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'geo_lat' => ['nullable', 'numeric'],
            'geo_lng' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
            'product_ids' => ['array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $location->update($data);
        $location->products()->sync($data['product_ids'] ?? []);

        return redirect()->route('management.locations.index')->with('status', 'Location updated.');
    }
}
