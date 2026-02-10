@extends('layouts.app')

@section('page_title', 'Add Location')
@section('page_desc', 'Register a new activation outlet and assign products.')
@section('page_actions')
    <a href="{{ route('customer.locations.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.locations.store') }}" class="form-section">
    @csrf
        @php
            $mapsKey = config('services.google.maps_key');
            $mapId = 'location-map-create';
            $searchId = 'location-search-create';
        @endphp
        <div class="section-block">
            <div class="stat-label">Map Picker</div>
            @if (!$mapsKey)
                <div class="alert alert-error">Google Maps key missing. Add GOOGLE_MAPS_KEY to your .env.</div>
            @endif
            <div class="form-grid">
                <div class="form-group">
                    <label>Search Place</label>
                    <input type="text" id="{{ $searchId }}" class="input" placeholder="Search address or place">
                </div>
                <div class="form-group">
                    <label>Geofence Radius (meters)</label>
                    <input type="number" name="geofence_radius" value="{{ old('geofence_radius') }}" class="input" min="0" step="1" placeholder="e.g. 50">
                </div>
            </div>
            <div class="map-shell">
                <div
                    id="{{ $mapId }}"
                    class="map-canvas"
                    data-map="location"
                    data-lat-field="geo_lat"
                    data-lng-field="geo_lng"
                    data-address-field="address"
                    data-radius-field="geofence_radius"
                    data-search-input="{{ $searchId }}"
                    data-lat="{{ old('geo_lat') }}"
                    data-lng="{{ old('geo_lng') }}"
                ></div>
            </div>
            <div class="map-hint">Click the map to set the location. Address and coordinates will auto-fill.</div>
        </div>

        <div class="section-block">
            <div class="stat-label">Location Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-grid three-col">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="{{ old('state') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>District</label>
                    <input type="text" name="district" value="{{ old('district') }}" class="input" required>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="textarea" rows="3">{{ old('address') }}</textarea>
            </div>
        </div>

        <div class="section-block">
            <div class="stat-label">Geo Coordinates</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="geo_lat" value="{{ old('geo_lat') }}" class="input">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="geo_lng" value="{{ old('geo_lng') }}" class="input">
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Create Location</button>
    </form>

    @if ($mapsKey)
        <script>
            window.__mapsReady = false;
            window.initLocationMap = function () {
                window.__mapsReady = true;
                if (window.__initLocationMap) {
                    window.__initLocationMap();
                }
            };
        </script>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&callback=initLocationMap&loading=async"></script>
    @endif
@endsection
