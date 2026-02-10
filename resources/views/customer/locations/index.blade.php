@extends('layouts.app')

@section('page_title', 'Locations')
@section('page_desc', 'Manage activation locations for your brand clients.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.locations.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search location name">
            </div>
            <div class="form-group">
                <label>Country</label>
                <select name="country" class="select">
                    <option value="">All countries</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>District</label>
                <select name="district" class="select">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="filters-actions">
            <div class="filters-buttons">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('customer.locations.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.locations.create') }}" class="btn btn-primary">Add Location</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->address ?? '-' }}</td>
                        <td>{{ ucfirst($location->status) }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.locations.edit', $location) }}" class="btn-icon" title="Edit" aria-label="Edit location">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted text-center">No locations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $locations->links() }}
@endsection
