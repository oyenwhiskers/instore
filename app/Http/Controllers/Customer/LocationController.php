<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $query = Location::where('company_id', $companyId)->with('brandClients');
        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $countrySearch = trim((string) $request->input('country'));
        if ($countrySearch !== '') {
            $query->where('country', 'like', '%' . $countrySearch . '%');
        }

        $districtSearch = trim((string) $request->input('district'));
        if ($districtSearch !== '') {
            $query->where('district', 'like', '%' . $districtSearch . '%');
        }

        $status = $request->input('status');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $locations = $query->orderBy('name')->paginate(20)->withQueryString();

        $countries = Location::where('company_id', $companyId)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->orderBy('country')
            ->distinct()
            ->pluck('country');

        $districts = Location::where('company_id', $companyId)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->orderBy('district')
            ->distinct()
            ->pluck('district');

        return view('customer.locations.index', [
            'locations' => $locations,
            'countries' => $countries,
            'districts' => $districts,
            'filters' => [
                'search' => $search,
                'country' => $countrySearch,
                'district' => $districtSearch,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('customer.locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $plan = $request->user()->company?->plan;

        if ($plan && $plan->max_locations) {
            $currentCount = Location::where('company_id', $companyId)->count();
            if ($currentCount >= $plan->max_locations) {
                return back()->withErrors([
                    'limit' => 'Location limit reached for the current plan.',
                ])->withInput();
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'geo_lat' => ['nullable', 'numeric'],
            'geo_lng' => ['nullable', 'numeric'],
            'geofence_radius' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $location = Location::create([
            'company_id' => $companyId,
            'brand_client_id' => null,
            'name' => $data['name'],
            'country' => $data['country'],
            'state' => $data['state'],
            'district' => $data['district'],
            'address' => $data['address'] ?? null,
            'geo_lat' => $data['geo_lat'] ?? null,
            'geo_lng' => $data['geo_lng'] ?? null,
            'geofence_radius' => $data['geofence_radius'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('customer.locations.index')->with('status', 'Location created.');
    }

    public function edit(Location $location): View
    {
        if ($location->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        return view('customer.locations.edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        if ($location->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'geo_lat' => ['nullable', 'numeric'],
            'geo_lng' => ['nullable', 'numeric'],
            'geofence_radius' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $location->update([
            'name' => $data['name'],
            'country' => $data['country'],
            'state' => $data['state'],
            'district' => $data['district'],
            'address' => $data['address'] ?? null,
            'geo_lat' => $data['geo_lat'] ?? null,
            'geo_lng' => $data['geo_lng'] ?? null,
            'geofence_radius' => $data['geofence_radius'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('customer.locations.index')->with('status', 'Location updated.');
    }
}
