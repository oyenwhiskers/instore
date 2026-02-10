@extends('layouts.app')

@section('page_title', 'Locations')
@section('page_desc', 'Maintain outlet records with compliance-ready status tracking.')
@section('page_actions')
    <a href="{{ route('management.locations.create') }}" class="btn btn-primary">Add Location</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($locations as $location)
                <tr>
                    <td>{{ $location->name }}</td>
                    <td>{{ $location->address }}</td>
                    <td>{{ ucfirst($location->status) }}</td>
                    <td><a href="{{ route('management.locations.edit', $location) }}" class="btn btn-ghost">Edit</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No locations found.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

{{ $locations->links() }}
@endsection
