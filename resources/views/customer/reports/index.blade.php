@extends('layouts.app')

@section('page_title', 'Reports')
@section('page_desc', 'Executive-ready sales summaries by event and brand, with daily KPI performance and action insights.')
@section('page_actions')
    <a href="{{ route('customer.reports.export', request()->query()) }}" class="btn btn-secondary">Export CSV</a>
    <a href="{{ route('customer.reports.export.pdf', request()->query()) }}" class="btn btn-secondary" target="_blank" rel="noopener">Export PDF</a>
@endsection

@section('content')
<div class="card" style="margin-bottom: 16px;">
    <form method="GET" class="form-section">
        <div class="stat-label">Filters</div>
        <div class="form-grid">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="input">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All</option>
                    <option value="active" @selected(($filters['status'] ?? 'all') === 'active')>Active</option>
                    <option value="upcoming" @selected(($filters['status'] ?? 'all') === 'upcoming')>Upcoming</option>
                    <option value="completed" @selected(($filters['status'] ?? 'all') === 'completed')>Completed</option>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button class="btn btn-primary" type="submit">Apply Filters</button>
            </div>
        </div>
    </form>
</div>

@php
    $statusCounts = $summaries->groupBy('status.overall')->map->count();
    $aboveCount = $statusCounts->get('above', 0);
    $onTrackCount = $statusCounts->get('on_track', 0);
    $belowCount = $statusCounts->get('below', 0);
    $totalCount = max($summaries->count(), 1);
    $abovePct = round(($aboveCount / $totalCount) * 100, 1);
    $onTrackPct = round(($onTrackCount / $totalCount) * 100, 1);
    $belowPct = round(($belowCount / $totalCount) * 100, 1);
    $highestRisk = $summaries->filter(fn($s) => $s['status']['overall'] === 'below')->take(3);
@endphp

<div class="report-summary-grid">
    <div class="card report-summary-card">
        <div class="stat-label">Total Sales (Filtered)</div>
        <div class="stat-value">RM {{ number_format($totals['sales'], 2) }}</div>
        <div class="text-xs muted">Aggregated from daily brand performance.</div>
    </div>
    <div class="card report-summary-card">
        <div class="stat-label">Premiums Redeemed</div>
        <div class="stat-value">{{ number_format($totals['redemptions']) }}</div>
        <div class="text-xs muted">Redemptions tracked across all active days.</div>
    </div>
    <div class="card report-summary-card">
        <div class="stat-label">KPI Status Mix</div>
        <div class="status-metric-row">
            <div class="status-metric">
                <span class="status-badge status-above">Above</span>
                <div class="status-metric-value">{{ $aboveCount }}</div>
            </div>
            <div class="status-metric">
                <span class="status-badge status-on_track">On Track</span>
                <div class="status-metric-value">{{ $onTrackCount }}</div>
            </div>
            <div class="status-metric">
                <span class="status-badge status-below">Below</span>
                <div class="status-metric-value">{{ $belowCount }}</div>
            </div>
        </div>
        <div class="status-bar" style="margin-top: 12px;">
            <div class="status-bar-fill status-above" style="width: {{ $abovePct }}%"></div>
            <div class="status-bar-fill status-on_track" style="width: {{ $onTrackPct }}%"></div>
            <div class="status-bar-fill status-below" style="width: {{ $belowPct }}%"></div>
        </div>
    </div>
    <div class="card report-summary-card">
        <div class="stat-label">Critical Highlights</div>
        @if ($highestRisk->isNotEmpty())
            <ul class="report-highlight-list">
                @foreach ($highestRisk as $risk)
                    <li>
                        <div class="highlight-title">{{ $risk['event']->name }} • {{ $risk['brand'] }}</div>
                        <div class="highlight-sub">{{ \Carbon\Carbon::parse($risk['date'])->format('d M Y') }} — {{ $risk['insight'] }}</div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="muted text-sm">No critical exceptions detected.</div>
        @endif
    </div>
</div>

@php
    $eventGroups = $summaries->groupBy(fn($item) => $item['event']->id);
@endphp

@forelse ($eventGroups as $eventId => $eventSummaries)
    @php
        $event = $eventSummaries->first()['event'];
        $eventStatus = $eventSummaries->first()['event_status'] ?? 'completed';
        $brandGroups = $eventSummaries->groupBy('brand');
        $statusClass = $eventStatus === 'upcoming' ? 'status-planned' : 'status-' . $eventStatus;
    @endphp
    <div class="card report-event-card" style="margin-bottom: 20px;">
        <div class="card-header" style="align-items: flex-start;">
            <div>
                <h3 class="card-title">{{ $event->name }}</h3>
                <div class="card-subtitle">
                    {{ $event->location?->name ?? '-' }} • {{ $event->start_date->format('d M') }} - {{ $event->end_date->format('d M Y') }}
                </div>
                <div class="text-xs muted" style="margin-top: 4px;">
                    {{ $event->promoters->count() }} promoters • {{ $event->products->count() }} products • {{ $event->premiums->count() }} premiums
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="status-badge {{ $statusClass }}">{{ ucfirst($eventStatus) }}</span>
                <a href="{{ route('customer.events.show', $event) }}" class="btn btn-ghost">View Event</a>
            </div>
        </div>

        @php
            $eventStatusCounts = $eventSummaries->groupBy('status.overall')->map->count();
            $eventAbove = $eventStatusCounts->get('above', 0);
            $eventOnTrack = $eventStatusCounts->get('on_track', 0);
            $eventBelow = $eventStatusCounts->get('below', 0);
            $eventTotal = max($eventSummaries->count(), 1);
        @endphp

        <div class="event-metric-row">
            <div class="event-metric">
                <div class="stat-label">Daily Summary Rows</div>
                <div class="stat-value">{{ $eventSummaries->count() }}</div>
            </div>
            <div class="event-metric">
                <div class="stat-label">Above KPI</div>
                <div class="stat-value" style="color: #15803d;">{{ $eventAbove }}</div>
            </div>
            <div class="event-metric">
                <div class="stat-label">On Track</div>
                <div class="stat-value" style="color: #b45309;">{{ $eventOnTrack }}</div>
            </div>
            <div class="event-metric">
                <div class="stat-label">Below KPI</div>
                <div class="stat-value" style="color: #b91c1c;">{{ $eventBelow }}</div>
            </div>
            <div class="event-metric">
                <div class="stat-label">KPI Mix</div>
                <div class="status-bar">
                    <div class="status-bar-fill status-above" style="width: {{ round(($eventAbove / $eventTotal) * 100, 1) }}%"></div>
                    <div class="status-bar-fill status-on_track" style="width: {{ round(($eventOnTrack / $eventTotal) * 100, 1) }}%"></div>
                    <div class="status-bar-fill status-below" style="width: {{ round(($eventBelow / $eventTotal) * 100, 1) }}%"></div>
                </div>
            </div>
        </div>

        @foreach ($brandGroups as $brandName => $brandSummaries)
            <div class="card" style="margin-top: 16px; background: #f8fafc;">
                <div class="card-header">
                    <div>
                        <div class="stat-label">Brand</div>
                        <div style="font-weight: 600; font-size: 16px;">{{ $brandName }}</div>
                    </div>
                    <div class="brand-insight-pill">Daily KPI Summary</div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>KPI Targets</th>
                                <th>Actual Performance</th>
                                <th>Status</th>
                                <th>Operational Notes</th>
                                <th>Insight / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brandSummaries->sortBy('date') as $summary)
                                @php
                                    $targets = $summary['targets'];
                                    $actuals = $summary['actuals'];
                                    $status = $summary['status'];
                                    $exceptions = collect($status)
                                        ->reject(fn($value, $key) => $key === 'overall' || $value !== 'below')
                                        ->keys()
                                        ->map(fn($key) => ucfirst($key) . ' below');
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($summary['date'])->format('d M Y') }}</td>
                                    <td>
                                        <div class="text-sm">
                                            Sales: RM {{ number_format($targets['sales'], 2) }}<br>
                                            Engagements: {{ number_format($targets['engagements']) }}<br>
                                            Samplings: {{ number_format($targets['samplings']) }}<br>
                                            Redemptions: {{ number_format($targets['redemptions']) }}<br>
                                            Conversion: {{ number_format($targets['conversion'], 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            Sales: RM {{ number_format($actuals['sales'], 2) }}<br>
                                            Engagements: {{ number_format($actuals['engagements']) }}<br>
                                            Samplings: {{ number_format($actuals['samplings']) }}<br>
                                            Redemptions: {{ number_format($actuals['redemptions']) }}<br>
                                            Conversion: {{ number_format($actuals['conversion'], 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $overallClass = $status['overall'] === 'on_track' ? 'status-on_track' : 'status-' . $status['overall'];
                                        @endphp
                                        <div style="display: grid; gap: 6px;">
                                            <span class="status-badge {{ $overallClass }}">{{ str_replace('_', ' ', ucfirst($status['overall'])) }}</span>
                                            @if ($exceptions->isNotEmpty())
                                                <div class="text-xs" style="color: #dc2626; font-weight: 600;">
                                                    {{ $exceptions->implode(' • ') }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if (!empty($summary['notes']))
                                            <ul class="text-sm" style="margin: 0; padding-left: 16px;">
                                                @foreach ($summary['notes'] as $note)
                                                    <li>{{ $note }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="muted">No issues flagged.</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-sm" style="font-weight: 600; color: #1f2937;">
                                            {{ $summary['insight'] }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@empty
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-text">No event summaries available for the selected filters.</div>
        </div>
    </div>
@endforelse
@endsection
