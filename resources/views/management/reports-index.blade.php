@extends('layouts.app')

@section('page_title', 'Hourly Reports')
@section('page_desc', 'Filter by date, location, and promoter for audit-ready tracking.')

@section('content')
<div class="card">
    <form method="GET" class="form-section">
        <div class="stat-label">Filters</div>
        <div class="form-grid">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="input">
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
                <th>Date</th>
                <th>Hour</th>
                <th>Promoter</th>
                <th>Location</th>
                <th>Sales</th>
                <th>Premiums</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                @php
                    $tier1 = $report->premiums->where('tier', 1)->sum('quantity');
                    $tier2 = $report->premiums->where('tier', 2)->sum('quantity');
                @endphp
                <tr>
                    <td>{{ $report->report_date->toDateString() }}</td>
                    <td>{{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                    <td>{{ $report->promoter?->name }}</td>
                    <td>{{ $report->location?->name }}</td>
                    <td>RM {{ number_format($report->total_sales_amount, 2) }}</td>
                    <td>T1 {{ $tier1 }} / T2 {{ $tier2 }}</td>
                    <td><a href="{{ route('management.reports.show', $report) }}" class="btn btn-ghost">View</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">No reports found.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

{{ $reports->links() }}
@endsection
