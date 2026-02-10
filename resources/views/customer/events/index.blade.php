@extends('layouts.app')

@section('page_title', 'Events')
@section('page_desc', 'Link promoters, locations, and products per activation event.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.events.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" class="input" placeholder="Search location name">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="planned" @selected(($filters['status'] ?? '') === 'planned')>Planned</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                </select>
            </div>
        </div>
        <div class="filters-actions">
            <div class="filters-buttons">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('customer.events.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.events.create') }}" class="btn btn-primary">Add Event</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-right">Promoters</th>
                    <th class="text-right">Products</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td>{{ $event->name }}</td>
                        <td>{{ $event->location?->name ?? '-' }}</td>
                        <td>{{ $event->start_date?->format('d M Y') }} - {{ $event->end_date?->format('d M Y') }}</td>
                        <td>{{ ucfirst($event->status) }}</td>
                        <td class="text-right">{{ $event->promoters->count() }}</td>
                        <td class="text-right">{{ $event->products->count() }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.events.show', $event) }}" class="btn-icon" title="View" aria-label="View event">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M10 4c4.4 0 7.4 3.2 8.4 5-.9 1.8-4 5-8.4 5S2.6 10.8 1.6 9c1-1.8 4-5 8.4-5zm0 2.2A2.8 2.8 0 1 0 10 13.8 2.8 2.8 0 0 0 10 6.2zm0 1.6A1.2 1.2 0 1 1 10 10.2 1.2 1.2 0 0 1 10 7.8z" fill="currentColor"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('customer.events.edit', $event) }}" class="btn-icon" title="Edit" aria-label="Edit event">
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
                        <td colspan="7" class="muted text-center">No events found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $events->links() }}
@endsection
