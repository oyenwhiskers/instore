@extends('layouts.app')

@section('page_title', 'Event Reports')
@section('page_desc', 'Sales summaries by event with quick access to full performance details.')

@section('content')
<div class="card" style="margin-bottom: 16px;">
    <div class="form-section" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        <div class="stat-label">Event Status</div>
        <div class="tab-row" role="tablist" style="gap: 8px; flex-wrap: wrap;">
            <a class="tab-button {{ $status === 'active' ? 'is-active' : '' }}" href="{{ route('management.reports.index', ['status' => 'active']) }}">Active <span class="tab-badge">{{ $statusCounts['active'] }}</span></a>
            <a class="tab-button {{ $status === 'upcoming' ? 'is-active' : '' }}" href="{{ route('management.reports.index', ['status' => 'upcoming']) }}">Upcoming <span class="tab-badge">{{ $statusCounts['upcoming'] }}</span></a>
            <a class="tab-button {{ $status === 'completed' ? 'is-active' : '' }}" href="{{ route('management.reports.index', ['status' => 'completed']) }}">Completed <span class="tab-badge">{{ $statusCounts['completed'] }}</span></a>
            <a class="tab-button {{ $status === 'all' ? 'is-active' : '' }}" href="{{ route('management.reports.index', ['status' => 'all']) }}">All <span class="tab-badge">{{ $statusCounts['all'] }}</span></a>
        </div>
    </div>
</div>

<div class="card-grid" style="margin-bottom: 16px;">
    <div class="card">
        <div class="stat-label">Events in View</div>
        <div class="stat-value">{{ $statusCounts[$status] ?? $statusCounts['all'] }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Active Events</div>
        <div class="stat-value">{{ $statusCounts['active'] }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Total Sales</div>
        <div class="stat-value">RM {{ number_format($totalSalesAll, 2) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Premiums Redeemed</div>
        <div class="stat-value">{{ number_format($totalPremiumsAll) }}</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Location</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th class="text-right">Sales</th>
                    <th class="text-right">Engagements</th>
                    <th class="text-right">Samplings</th>
                    <th class="text-right">Premiums</th>
                    <th class="text-right">Reports</th>
                    <th>Last Submission</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($eventSummaries as $summary)
                    @php
                        $event = $summary['event'];
                        $lastReport = $summary['last_report'];
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $event->name }}</div>
                            <div class="text-xs muted">{{ $event->promoters->count() }} promoters • {{ $event->products->count() }} products</div>
                        </td>
                        <td>{{ $event->location?->name ?? '-' }}</td>
                        <td>{{ $event->start_date->format('d M') }} - {{ $event->end_date->format('d M Y') }}</td>
                        <td>
                            @php
                                $statusClass = $summary['status'] === 'upcoming'
                                    ? 'status-planned'
                                    : 'status-' . $summary['status'];
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ ucfirst($summary['status']) }}
                            </span>
                        </td>
                        <td class="text-right">RM {{ number_format($summary['total_sales'], 2) }}</td>
                        <td class="text-right">{{ number_format($summary['total_engagements']) }}</td>
                        <td class="text-right">{{ number_format($summary['total_samplings']) }}</td>
                        <td class="text-right">{{ number_format($summary['total_premiums']) }}</td>
                        <td class="text-right">{{ number_format($summary['total_reports']) }}</td>
                        <td>
                            @if ($lastReport)
                                <div class="text-xs">{{ $lastReport->created_at?->format('d M Y') }}</div>
                                <div class="text-xs muted">{{ $lastReport->created_at?->format('H:i') }}</div>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                        <td><a href="{{ route('management.reports.show', $event) }}" class="btn btn-ghost">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="muted">No events available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
