@extends('layouts.app')

@section('page_title', 'Customer Dashboard')
@section('page_desc', 'Overview of performance and operational coverage for your company.')

@section('content')
<div class="card-grid">
    <div class="card">
        <div class="stat-label">Sales</div>
        <div class="stat-value">RM {{ number_format($totals['sales'], 2) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Engagements</div>
        <div class="stat-value">{{ $totals['engagements'] }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Samplings</div>
        <div class="stat-value">{{ $totals['samplings'] }}</div>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <div class="stat-label">Promoters</div>
        <div class="stat-value">{{ $promoterCount }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Locations</div>
        <div class="stat-value">{{ $locationCount }}</div>
    </div>
</div>

<div class="split-view">
    <div class="card">
        <div class="stat-label">Recent Reports</div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Promoter</th>
                        <th>Location</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentReports as $report)
                        <tr>
                            <td>{{ $report->report_date->toDateString() }} {{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                            <td>{{ $report->promoter?->name }}</td>
                            <td>{{ $report->location?->name }}</td>
                            <td>RM {{ number_format($report->total_sales_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No reports submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="stat-label">Top Locations</div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Sales</th>
                        <th>Engagements</th>
                        <th>Samplings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topLocations as $location)
                        <tr>
                            <td>{{ $location->location_name }}</td>
                            <td>RM {{ number_format($location->total_sales, 2) }}</td>
                            <td>{{ (int) $location->total_engagements }}</td>
                            <td>{{ (int) $location->total_samplings }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No location performance yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
