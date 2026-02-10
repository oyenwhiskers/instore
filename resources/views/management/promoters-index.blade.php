@extends('layouts.app')

@section('page_title', 'Promoters')
@section('page_desc', 'Manage promoter profiles, assignment status, and availability.')
@section('page_actions')
    <a href="{{ route('management.promoters.create') }}" class="btn btn-primary">Add Promoter</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Promoter ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Location</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($promoters as $promoter)
                <tr>
                    <td>{{ $promoter->promoter_id ?? '-' }}</td>
                    <td>{{ $promoter->name }}</td>
                    <td>{{ $promoter->email }}</td>
                    <td>{{ $promoter->promoterProfile?->location?->name ?? 'Unassigned' }}</td>
                    <td>{{ ucfirst($promoter->status) }}</td>
                    <td><a href="{{ route('management.promoters.edit', $promoter) }}" class="btn btn-ghost">Edit</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No promoters found.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

{{ $promoters->links() }}
@endsection
