@extends('layouts.app')

@section('page_title', $event->name . ' - Sales Summary')
@section('page_desc', 'Event-level performance snapshot with product, premium, and promoter insights.')
@section('page_actions')
    <a href="{{ route('management.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
@endsection

@section('content')
@php
    $totalPremiums = $premiumRedemptions->sum('quantity');
    $conversionRate = $actualEngagements > 0 ? ($actualSales / $actualEngagements) : 0;
    $avgSaleValue = $productSales->sum('quantity') > 0 ? ($actualSales / $productSales->sum('quantity')) : 0;
    $statusClass = now()->between($event->start_date, $event->end_date) ? 'status-active' : (now()->lt($event->start_date) ? 'status-planned' : 'status-completed');
    $statusLabel = now()->between($event->start_date, $event->end_date) ? 'Active' : (now()->lt($event->start_date) ? 'Upcoming' : 'Completed');
@endphp

<div class="card" style="margin-bottom: 16px;">
    <div class="form-grid text-sm">
        <div><strong>Location:</strong> {{ $event->location?->name ?? '-' }}</div>
        <div><strong>Dates:</strong> {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}</div>
        <div><strong>Promoters:</strong> {{ $event->promoters->count() }}</div>
        <div><strong>Products:</strong> {{ $event->products->count() }}</div>
        <div><strong>Premiums:</strong> {{ $event->premiums->count() }}</div>
        <div><strong>Status:</strong> <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
    </div>
</div>

<div class="card-grid" style="margin-bottom: 16px;">
    <div class="card">
        <div class="stat-label">Total Sales</div>
        <div class="stat-value">RM {{ number_format($actualSales, 2) }}</div>
        <div class="text-xs muted">Today: RM {{ number_format($todaySales, 2) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Engagements</div>
        <div class="stat-value">{{ number_format($actualEngagements) }}</div>
        <div class="text-xs muted">Today: {{ number_format($todayEngagements) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Samplings</div>
        <div class="stat-value">{{ number_format($actualSamplings) }}</div>
        <div class="text-xs muted">Today: {{ number_format($todaySamplings) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Premiums Redeemed</div>
        <div class="stat-value">{{ number_format($totalPremiums) }}</div>
        <div class="text-xs muted">Across event period</div>
    </div>
    <div class="card">
        <div class="stat-label">Conversion Rate</div>
        <div class="stat-value">RM {{ number_format($conversionRate, 2) }}</div>
        <div class="text-xs muted">Sales per engagement</div>
    </div>
    <div class="card">
        <div class="stat-label">Avg Sale Value</div>
        <div class="stat-value">RM {{ number_format($avgSaleValue, 2) }}</div>
        <div class="text-xs muted">Per unit sold</div>
    </div>
</div>

<div class="split-view">
    <div class="card">
        <div class="stat-label">Today Hourly Sales</div>
        @if ($hourlyBreakdown->isNotEmpty())
            <div class="hourly-chart" style="margin-top: 12px;">
                @foreach ($hourlyBreakdown as $hour => $data)
                    <div class="hourly-bar">
                        <div class="hourly-fill" style="height: {{ min(100, ($data['sales'] / max(1, $hourlyBreakdown->max('sales'))) * 100) }}%;"></div>
                        <div class="hourly-label">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="hourly-value">RM {{ number_format($data['sales'], 0) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="muted text-sm" style="margin-top: 12px;">No hourly data for today.</div>
        @endif
    </div>

    <div class="card">
        <div class="stat-label">Shift Coverage</div>
        <div class="text-sm" style="margin-top: 12px;">
            <div><strong>Expected Reports (this hour):</strong> {{ $expectedReports }}</div>
            <div><strong>Submitted:</strong> {{ $currentHourReports }}</div>
            <div><strong>Pending:</strong> {{ max(0, $expectedReports - $currentHourReports) }}</div>
        </div>
        <div class="stat-label" style="margin-top: 16px;">Stock Health</div>
        <div class="text-sm" style="margin-top: 8px;">
            <div><strong>Low Stock:</strong> {{ $lowStockProducts }}</div>
            <div><strong>Out of Stock:</strong> {{ $outOfStockProducts }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 16px;">
    <div class="stat-label">Top Products</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Units Sold</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productSales->take(8) as $product)
                    <tr>
                        <td>{{ $product['product']?->name ?? 'Unknown' }}</td>
                        <td class="text-right">{{ number_format($product['quantity']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="muted">No product sales recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 16px;">
    <div class="stat-label">Premium Redemptions</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Premium</th>
                    <th class="text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($premiumRedemptions as $redemption)
                    <tr>
                        <td>{{ $redemption['premium']?->gift_name ?? 'Premium' }}</td>
                        <td class="text-right">{{ number_format($redemption['quantity']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="muted">No premiums redeemed.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 16px;">
    <div class="stat-label">Promoter Performance</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Promoter</th>
                    <th class="text-right">Sales</th>
                    <th class="text-right">Engagements</th>
                    <th class="text-right">Samplings</th>
                    <th class="text-right">Reports</th>
                    <th>Last Report</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promoterPerformance as $performance)
                    <tr>
                        <td>{{ $performance['promoter']?->name ?? '-' }}</td>
                        <td class="text-right">RM {{ number_format($performance['sales'], 2) }}</td>
                        <td class="text-right">{{ number_format($performance['engagements']) }}</td>
                        <td class="text-right">{{ number_format($performance['samplings']) }}</td>
                        <td class="text-right">{{ number_format($performance['report_count']) }}</td>
                        <td>
                            @if ($performance['last_report'])
                                <div class="text-xs">{{ $performance['last_report']->report_date?->format('d M Y') }}</div>
                                <div class="text-xs muted">{{ str_pad($performance['last_report']->report_hour, 2, '0', STR_PAD_LEFT) }}:00</div>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No promoter reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 16px;">
    <div class="stat-label">Recent Submissions</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Promoter</th>
                    <th class="text-right">Sales</th>
                    <th class="text-right">Engagements</th>
                    <th class="text-right">Samplings</th>
                    <th>Premiums</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentReports as $report)
                    <tr>
                        <td>{{ $report->report_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                        <td>{{ $report->promoter?->name ?? '-' }}</td>
                        <td class="text-right">RM {{ number_format($report->total_sales_amount ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($report->engagements_count ?? 0) }}</td>
                        <td class="text-right">{{ number_format($report->samplings_count ?? 0) }}</td>
                        <td>
                            @if ($report->premiums->isNotEmpty())
                                {{ $report->premiums
                                    ->groupBy('premium_id')
                                    ->map(function ($items) {
                                        $name = $items->first()?->premium?->gift_name ?? 'Premium';
                                        $qty = $items->sum('quantity');
                                        return $name . ': ' . $qty;
                                    })
                                    ->values()
                                    ->implode(' | ') }}
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">No submissions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
