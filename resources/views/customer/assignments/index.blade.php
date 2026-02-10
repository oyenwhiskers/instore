@extends('layouts.app')

@section('page_title', 'Promoter Roster')
@section('page_desc', 'Review promoter assignments across locations and dates.')

@section('content')
<div class="card">
    <form method="GET" class="form-section">
        <div class="stat-label">Filters</div>
        <div class="form-grid">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="input">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="input">
            </div>
            <div class="form-group">
                <label>Location</label>
                <select name="location_id" class="select">
                    <option value="">All</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Promoter</label>
                <select name="promoter_user_id" class="select">
                    <option value="">All</option>
                    @foreach ($promoters as $promoter)
                        <option value="{{ $promoter->id }}" @selected(request('promoter_user_id') == $promoter->id)>{{ $promoter->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button class="btn btn-primary" type="submit">Apply Filters</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Promoter</th>
                    <th>Location</th>
                    <th>Dates</th>
                    <th>Hours</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->promoter?->name }}</td>
                        <td>{{ $assignment->location?->name }}</td>
                        <td>
                            {{ $assignment->start_date?->toDateString() ?? '-' }}
                            @if ($assignment->end_date)
                                to {{ $assignment->end_date->toDateString() }}
                            @endif
                        </td>
                        <td>
                            {{ $assignment->start_time ?? '-' }}
                            @if ($assignment->end_time)
                                to {{ $assignment->end_time }}
                            @endif
                        </td>
                        <td>{{ $assignment->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No assignments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $assignments->links() }}
@endsection
