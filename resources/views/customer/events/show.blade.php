@extends('layouts.app')

@section('page_title', 'Event Details')
@section('page_desc', 'Review the event setup and assignments.')
@section('page_actions')
    <a href="{{ route('customer.events.edit', $event) }}" class="btn btn-primary">Edit Event</a>
    <a href="{{ route('customer.events.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
@php
    $promoterCount = $event->promoters->count();
    $productCount = $event->products->count();
    $premiumCount = $event->premiums->count();
    $submissionCount = $hourlyReports->count();
    $activityItems = collect();
    $activityItems->push([
        'timestamp' => $event->created_at,
        'title' => 'Event created',
        'by' => $event->createdBy?->name,
        'details' => 'Status: ' . ucfirst($event->status),
    ]);

    if ($event->updated_at && $event->updated_at->ne($event->created_at)) {
        $activityItems->push([
            'timestamp' => $event->updated_at,
            'title' => 'Event updated',
            'by' => $event->updatedBy?->name,
            'details' => 'Status: ' . ucfirst($event->status),
        ]);
    }

    foreach ($stockMovements as $movement) {
        $activityItems->push([
            'timestamp' => $movement->created_at,
            'title' => $movement->movement_type === 'in' ? 'Stock in' : 'Stock out',
            'by' => $movement->createdBy?->name,
            'details' => ($movement->product?->name ?? 'Product') . ' x' . $movement->quantity,
        ]);
    }

    foreach ($promoterKpis as $kpi) {
        if (!$kpi->updated_at) {
            continue;
        }
        $activityItems->push([
            'timestamp' => $kpi->updated_at,
            'title' => 'KPI targets updated',
            'by' => $kpi->updatedBy?->name,
            'details' => $kpi->promoter?->name ?? 'Promoter',
        ]);
    }

    $activityItems = $activityItems
        ->filter(fn ($item) => (bool) $item['timestamp'])
        ->sortByDesc('timestamp');
    $activityCount = $activityItems->count();
@endphp

<div class="card event-header-card">
    <div class="event-header-grid">
        <div>
            <div class="stat-label">Event Name</div>
            <div class="stat-value">{{ $event->name }}</div>
        </div>
        <div>
            <div class="stat-label">Status</div>
            <div class="stat-value">
                <span class="status-badge status-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
            </div>
        </div>
        <div>
            <div class="stat-label">Location</div>
            <div class="stat-value stat-value-wrap">{{ $event->location?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Date Range</div>
            <div class="stat-value stat-value-inline">{{ $event->start_date?->format('d M Y') }} - {{ $event->end_date?->format('d M Y') }}</div>
        </div>
        <div>
            <div class="stat-label">Created By</div>
            <div class="stat-value stat-value-small">{{ $event->createdBy?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Updated By</div>
            <div class="stat-value stat-value-small">{{ $event->updatedBy?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Created At</div>
            <div class="stat-value stat-value-inline stat-value-small">{{ $event->created_at?->format('d M Y, H:i') ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Updated At</div>
            <div class="stat-value stat-value-inline stat-value-small">{{ $event->updated_at?->format('d M Y, H:i') ?? '-' }}</div>
        </div>
    </div>
    @if ($event->notes)
        <div class="form-group" style="margin-top: 16px;">
            <div class="stat-label">Notes</div>
            <div>{{ $event->notes }}</div>
        </div>
    @endif

    <div class="event-header-divider"></div>
    <div class="tabs tabs-inline" data-tabs data-tab-group="event-details" data-default-tab="{{ request('event_tab', 'overview') }}">
        <div class="tabs-nav" role="tablist">
            <button type="button" class="tab-button is-active" data-tab-target="overview" role="tab" aria-selected="true">
                Overview
            </button>
            <button type="button" class="tab-button" data-tab-target="promoters" role="tab" aria-selected="false">
                Promoters
                <span class="tab-badge">{{ $promoterCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="kpi" role="tab" aria-selected="false">
                KPI Targets
                <span class="tab-badge">{{ $promoterCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="products" role="tab" aria-selected="false">
                Products
                <span class="tab-badge">{{ $productCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="premiums" role="tab" aria-selected="false">
                Premiums
                <span class="tab-badge">{{ $premiumCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="submissions" role="tab" aria-selected="false">
                Submissions
                <span class="tab-badge">{{ $submissionCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="activity" role="tab" aria-selected="false">
                Activity Log
                <span class="tab-badge">{{ $activityCount }}</span>
            </button>
        </div>
    </div>
</div>

@php
    // Calculate Overview Metrics
    $totalPromoters = $event->promoters->count();
    $checkedInCount = $attendanceByPromoter->filter(fn($att) => ($att['count'] ?? 0) > 0)->count();
    $missingCount = $totalPromoters - $checkedInCount;
    
    // KPI Progress
    $totalSalesTarget = $promoterKpis->sum('target_sales_amount') ?: 1;
    $totalEngagementsTarget = $promoterKpis->sum('target_engagements') ?: 1;
    $totalSamplingsTarget = $promoterKpis->sum('target_samplings') ?: 1;
    
    // Progress percentages
    $salesProgress = ($actualSales / $totalSalesTarget) * 100;
    $engagementsProgress = ($actualEngagements / $totalEngagementsTarget) * 100;
    $samplingsProgress = ($actualSamplings / $totalSamplingsTarget) * 100;
    
    // Stock Status
    $lowStockProducts = $stockBalances->filter(fn($balance) => $balance > 0 && $balance < 10)->count();
    $outOfStockProducts = $stockBalances->filter(fn($balance) => $balance <= 0)->count();
    $totalProducts = $event->products->count();
    $stockHealthy = $totalProducts - $lowStockProducts - $outOfStockProducts;
    
    // Total premiums redeemed
    $totalPremiumsRedeemed = $premiumRedemptions->sum('quantity');
    
    // Conversion rate
    $conversionRate = $actualEngagements > 0 ? ($actualSales / $actualEngagements) : 0;
    
    // Average sale value
    $avgSaleValue = $productSales->sum('quantity') > 0 ? ($actualSales / $productSales->sum('quantity')) : 0;
@endphp

<div class="tab-panel is-active" data-tab-panel="overview" data-tab-group="event-details">
<div class="tab-panel is-active" data-tab-panel="overview" data-tab-group="event-details">
    <div class="overview-grid-main">
        <!-- Primary KPI Cards Row -->
        <div class="overview-kpi-row">
            <!-- Sales Performance -->
            <div class="card overview-kpi-card">
                <div class="overview-kpi-header">
                    <div class="overview-kpi-icon" style="background: #e7eef9; color: #1e4f8f;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-kpi-label">Total Sales</div>
                        <div class="overview-kpi-subtitle">Event to date</div>
                    </div>
                </div>
                <div class="overview-kpi-value">RM {{ number_format($actualSales, 2) }}</div>
                <div class="overview-kpi-target">Target: RM {{ number_format($totalSalesTarget, 2) }}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ min($salesProgress, 100) }}%; background: #1e4f8f;"></div>
                </div>
                <div class="overview-kpi-footer">
                    <span class="kpi-progress-text" style="color: {{ $salesProgress >= 100 ? '#0f6d57' : ($salesProgress >= 50 ? '#d97706' : '#dc2626') }}">
                        {{ number_format($salesProgress, 1) }}% achieved
                    </span>
                    <span class="kpi-today-text">Today: RM {{ number_format($todaySales, 2) }}</span>
                </div>
            </div>

            <!-- Engagements Performance -->
            <div class="card overview-kpi-card">
                <div class="overview-kpi-header">
                    <div class="overview-kpi-icon" style="background: #f0e7f9; color: #7c3aed;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-kpi-label">Total Engagements</div>
                        <div class="overview-kpi-subtitle">Customer interactions</div>
                    </div>
                </div>
                <div class="overview-kpi-value">{{ number_format($actualEngagements) }}</div>
                <div class="overview-kpi-target">Target: {{ number_format($totalEngagementsTarget) }}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ min($engagementsProgress, 100) }}%; background: #7c3aed;"></div>
                </div>
                <div class="overview-kpi-footer">
                    <span class="kpi-progress-text" style="color: {{ $engagementsProgress >= 100 ? '#0f6d57' : ($engagementsProgress >= 50 ? '#d97706' : '#dc2626') }}">
                        {{ number_format($engagementsProgress, 1) }}% achieved
                    </span>
                    <span class="kpi-today-text">Today: {{ number_format($todayEngagements) }}</span>
                </div>
            </div>

            <!-- Samplings Performance -->
            <div class="card overview-kpi-card">
                <div class="overview-kpi-header">
                    <div class="overview-kpi-icon" style="background: #fef3e7; color: #ea580c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85v5.65z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-kpi-label">Total Samplings</div>
                        <div class="overview-kpi-subtitle">Products distributed</div>
                    </div>
                </div>
                <div class="overview-kpi-value">{{ number_format($actualSamplings) }}</div>
                <div class="overview-kpi-target">Target: {{ number_format($totalSamplingsTarget) }}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ min($samplingsProgress, 100) }}%; background: #ea580c;"></div>
                </div>
                <div class="overview-kpi-footer">
                    <span class="kpi-progress-text" style="color: {{ $samplingsProgress >= 100 ? '#0f6d57' : ($samplingsProgress >= 50 ? '#d97706' : '#dc2626') }}">
                        {{ number_format($samplingsProgress, 1) }}% achieved
                    </span>
                    <span class="kpi-today-text">Today: {{ number_format($todaySamplings) }}</span>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics Row -->
        <div class="overview-metrics-row">
            <div class="card overview-metric-card">
                <div class="metric-icon" style="background: #e7f3ef; color: #0f6d57;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                    </svg>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Team Present</div>
                    <div class="metric-value">{{ $checkedInCount }}/{{ $totalPromoters }}</div>
                    @if($missingCount > 0)
                    <div class="metric-detail" style="color: #d97706;">{{ $missingCount }} not checked in</div>
                    @endif
                </div>
            </div>

            <div class="card overview-metric-card">
                <div class="metric-icon" style="background: #e7eef9; color: #1e4f8f;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
                    </svg>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Conversion Rate</div>
                    <div class="metric-value">{{ number_format($conversionRate * 100, 1) }}%</div>
                    <div class="metric-detail">Sales per engagement</div>
                </div>
            </div>

            <div class="card overview-metric-card">
                <div class="metric-icon" style="background: #fef3e7; color: #ea580c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                    </svg>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Premiums Redeemed</div>
                    <div class="metric-value">{{ number_format($totalPremiumsRedeemed) }}</div>
                    <div class="metric-detail">Total gifts distributed</div>
                </div>
            </div>

            <div class="card overview-metric-card">
                <div class="metric-icon" style="background: #f0e7f9; color: #7c3aed;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.5 0A1.5 1.5 0 0 0 1 1.5v13A1.5 1.5 0 0 0 2.5 16h11a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 13.5 0h-11zM2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5V13h-1v-1.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5V13H2V1.5z"/>
                    </svg>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Stock Status</div>
                    <div class="metric-value">{{ $stockHealthy }}/{{ $totalProducts }}</div>
                    @if($lowStockProducts > 0 || $outOfStockProducts > 0)
                    <div class="metric-detail" style="color: #dc2626;">
                        @if($outOfStockProducts > 0) {{ $outOfStockProducts }} out, @endif
                        @if($lowStockProducts > 0) {{ $lowStockProducts }} low @endif
                    </div>
                    @else
                    <div class="metric-detail" style="color: #0f6d57;">All stock healthy</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="overview-content-grid">
            <!-- Promoter Performance -->
            <div class="card overview-full-card">
                <div class="card-header">
                    <h3 class="card-title">Promoter Performance</h3>
                    <div class="card-subtitle">Individual contribution & progress</div>
                </div>
                <div class="promoter-performance-list">
                    @forelse($promoterPerformance->take(10) as $perf)
                    <div class="promoter-perf-item">
                        <div class="promoter-perf-header">
                            <div class="promoter-perf-name">
                                {{ $perf['promoter']?->name ?? 'Unknown' }}
                                <span class="promoter-perf-reports">{{ $perf['report_count'] }} reports</span>
                            </div>
                            <div class="promoter-perf-sales">RM {{ number_format($perf['sales'], 2) }}</div>
                        </div>
                        <div class="promoter-perf-metrics">
                            <div class="perf-metric-small">
                                <span class="perf-label">Sales</span>
                                <div class="perf-bar-mini">
                                    <div class="perf-fill-mini" style="width: {{ min($perf['sales_progress'], 100) }}%; background: #1e4f8f;"></div>
                                </div>
                                <span class="perf-value">{{ number_format($perf['sales_progress'], 0) }}%</span>
                            </div>
                            <div class="perf-metric-small">
                                <span class="perf-label">Engagements</span>
                                <div class="perf-bar-mini">
                                    <div class="perf-fill-mini" style="width: {{ min($perf['engagements_progress'], 100) }}%; background: #7c3aed;"></div>
                                </div>
                                <span class="perf-value">{{ number_format($perf['engagements']) }}/{{ number_format($perf['engagements_target']) }}</span>
                            </div>
                            <div class="perf-metric-small">
                                <span class="perf-label">Samplings</span>
                                <div class="perf-bar-mini">
                                    <div class="perf-fill-mini" style="width: {{ min($perf['samplings_progress'], 100) }}%; background: #ea580c;"></div>
                                </div>
                                <span class="perf-value">{{ number_format($perf['samplings']) }}/{{ number_format($perf['samplings_target']) }}</span>
                            </div>
                        </div>
                        @if($perf['last_report'])
                        <div class="promoter-perf-last-report">
                            Last report: {{ $perf['last_report']->report_date?->format('M d') }} {{ $perf['last_report']->report_hour }}:00
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="empty-state">
                        <div class="empty-state-text">No performance data yet</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Products</h3>
                    <div class="card-subtitle">Best performers</div>
                </div>
                <div class="product-sales-list">
                    @forelse($productSales->take(8) as $ps)
                    <div class="product-sale-item">
                        <div class="product-rank">{{ $loop->iteration }}</div>
                        <div class="product-info">
                            <div class="product-name">{{ $ps['product']?->name ?? 'Unknown Product' }}</div>
                            <div class="product-qty">{{ number_format($ps['quantity']) }} units</div>
                        </div>
                        @php
                            $stock = $stockBalances->get($ps['product']?->id, 0);
                        @endphp
                        <div class="product-stock {{ $stock <= 0 ? 'stock-out' : ($stock < 10 ? 'stock-low' : 'stock-ok') }}">
                            {{ $stock }} left
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <div class="empty-state-text">No sales data</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Hourly Trend -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Today's Hourly Performance</h3>
                    <div class="card-subtitle">Real-time activity tracking</div>
                </div>
                @if($hourlyBreakdown->count() > 0)
                <div class="hourly-chart">
                    @foreach($hourlyBreakdown as $hour => $data)
                    <div class="hourly-bar-wrapper">
                        <div class="hourly-bar-container">
                            @php
                                $maxSales = $hourlyBreakdown->pluck('sales')->max() ?: 1;
                                $height = ($data['sales'] / $maxSales) * 100;
                            @endphp
                            <div class="hourly-bar" style="height: {{ $height }}%; background: #1e4f8f;" title="RM {{ number_format($data['sales'], 2) }}"></div>
                        </div>
                        <div class="hourly-label">{{ $hour }}:00</div>
                        <div class="hourly-value">RM {{ number_format($data['sales'], 0) }}</div>
                    </div>
                    @endforeach
                </div>
                @if($peakHour)
                <div class="hourly-insight">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                    Peak hour: {{ $peakHour }}:00 with RM {{ number_format($peakSales, 2) }} in sales
                </div>
                @endif
                @else
                <div class="empty-state">
                    <div class="empty-state-text">No hourly data for today</div>
                </div>
                @endif
            </div>

            <!-- Premium Breakdown -->
            @if($premiumRedemptions->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Premium Redemptions</h3>
                    <div class="card-subtitle">By premium</div>
                </div>
                <div class="premium-list">
                    @foreach($premiumRedemptions as $redemption)
                    <div class="premium-item">
                        <div class="premium-tier">{{ $redemption['premium']?->gift_name ?? 'Premium' }}</div>
                        <div class="premium-bar">
                            @php
                                $maxQty = $premiumRedemptions->max('quantity') ?: 1;
                                $width = ($redemption['quantity'] / $maxQty) * 100;
                            @endphp
                            <div class="premium-fill" style="width: {{ $width }}%; background: linear-gradient(90deg, #7c3aed, #a78bfa);"></div>
                        </div>
                        <div class="premium-qty">{{ number_format($redemption['quantity']) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Alerts & Issues -->
            @php
                $alerts = collect();
                if ($missingCount > 0) {
                    $alerts->push(['type' => 'warning', 'message' => "{$missingCount} promoter(s) not checked in"]);
                }
                if ($outOfStockProducts > 0) {
                    $alerts->push(['type' => 'critical', 'message' => "{$outOfStockProducts} product(s) out of stock"]);
                }
                if ($lowStockProducts > 0) {
                    $alerts->push(['type' => 'warning', 'message' => "{$lowStockProducts} product(s) running low"]);
                }
                if ($currentHourReports < $expectedReports && $expectedReports > 0) {
                    $pending = $expectedReports - $currentHourReports;
                    $alerts->push(['type' => 'info', 'message' => "Awaiting {$pending} hourly report(s)"]);
                }
                $underperforming = $promoterPerformance->filter(fn($p) => $p['sales_progress'] < 30 && $p['sales_target'] > 0)->count();
                if ($underperforming > 0) {
                    $alerts->push(['type' => 'warning', 'message' => "{$underperforming} promoter(s) below 30% target"]);
                }
            @endphp
            @if($alerts->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Alerts & Actions Needed</h3>
                    <div class="card-subtitle">Items requiring attention</div>
                </div>
                <div class="alerts-list">
                    @foreach($alerts as $alert)
                    <div class="alert-item alert-{{ $alert['type'] }}">
                        <div class="alert-icon">
                            @if($alert['type'] === 'critical')
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                            </svg>
                            @elseif($alert['type'] === 'warning')
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                            </svg>
                            @endif
                        </div>
                        <div class="alert-message">{{ $alert['message'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="tab-panel" data-tab-panel="promoters" data-tab-group="event-details">
        <!-- Team Status Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Team Status</h3>
            </div>
            <div class="overview-stats">
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #e7f3ef; color: #0f6d57;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Checked In</div>
                        <div class="overview-stat-value">{{ $checkedInCount }}/{{ $totalPromoters }}</div>
                    </div>
                </div>
                
                @if($missingCount > 0)
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #fff4e6; color: #d97706;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Missing</div>
                        <div class="overview-stat-value" style="color: #d97706;">{{ $missingCount }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- KPI Targets Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">KPI Targets</h3>
            </div>
            <div class="overview-stats">
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #e7eef9; color: #1e4f8f;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Sales Target</div>
                        <div class="overview-stat-value">RM {{ number_format($totalSalesTarget, 2) }}</div>
                    </div>
                </div>
                
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #f0e7f9; color: #7c3aed;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Engagements</div>
                        <div class="overview-stat-value">{{ number_format($totalEngagementsTarget) }}</div>
                    </div>
                </div>
                
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #fef3e7; color: #ea580c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85v5.65z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Samplings</div>
                        <div class="overview-stat-value">{{ number_format($totalSamplingsTarget) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Status Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock Status</h3>
            </div>
            <div class="overview-stats">
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #e7f3ef; color: #0f6d57;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Healthy Stock</div>
                        <div class="overview-stat-value">{{ $stockHealthy }}/{{ $totalProducts }}</div>
                    </div>
                </div>
                
                @if($lowStockProducts > 0)
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #fff4e6; color: #d97706;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Low Stock</div>
                        <div class="overview-stat-value" style="color: #d97706;">{{ $lowStockProducts }}</div>
                    </div>
                </div>
                @endif
                
                @if($outOfStockProducts > 0)
                <div class="overview-stat">
                    <div class="overview-stat-icon" style="background: #fef2f2; color: #dc2626;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="overview-stat-label">Out of Stock</div>
                        <div class="overview-stat-value" style="color: #dc2626;">{{ $outOfStockProducts }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="tab-panel" data-tab-panel="promoters" data-tab-group="event-details">
    <div class="card">
        <form method="GET" action="{{ route('customer.events.show', $event) }}" class="filters-bar" style="margin-bottom: 12px;" data-auto-filter data-filter-target="[data-tab-panel='promoters']">
            <input type="hidden" name="event_tab" value="promoters">
            <input type="hidden" name="promoter_tab" value="{{ request('promoter_tab', 'attendance') }}" data-tab-store="promoter-views">
            <div class="filters-actions" style="width: 100%;">
                <div class="filters-grid" style="flex: 1;">
                    <div class="form-group">
                        <input type="text" name="attendance_search" value="{{ $attendanceFilters['search'] ?? '' }}" class="input" placeholder="Search promoter name">
                    </div>
                    <div class="form-group">
                        <select name="attendance_status" class="select">
                            <option value="">All status</option>
                            <option value="checked" @selected(($attendanceFilters['status'] ?? '') === 'checked')>Checked in</option>
                            <option value="missing" @selected(($attendanceFilters['status'] ?? '') === 'missing')>Not checked in</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="date" name="attendance_date" value="{{ $attendanceFilters['date'] ?? '' }}" class="input">
                    </div>
                </div>
                <div class="tabs" data-tabs data-tab-group="promoter-views" data-default-tab="{{ request('promoter_tab', 'attendance') }}">
                    <div class="tabs-nav" role="tablist">
                        <button type="button" class="tab-button is-active" data-tab-target="attendance" role="tab" aria-selected="true">Attendance</button>
                        <button type="button" class="tab-button" data-tab-target="schedule" role="tab" aria-selected="false">Schedule</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="tab-panel" data-tab-panel="attendance" data-tab-group="promoter-views">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Last Check-in</th>
                            <th class="table-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendancePromoters as $promoter)
                            @php
                                $attendance = $attendanceByPromoter->get($promoter->id, [
                                    'count' => 0,
                                    'last_check_in' => null,
                                ]);
                                $hasCheckin = ($attendance['count'] ?? 0) > 0;
                                $latestCheckin = $latestCheckinsByPromoter->get($promoter->id);
                                $checkinImageUrl = $latestCheckin?->image_path
                                    ? \Illuminate\Support\Facades\Storage::url($latestCheckin->image_path)
                                    : null;
                            @endphp
                            <tr>
                                <td>{{ $promoter->name }}</td>
                                <td>
                                    <span class="attendance-badge {{ $hasCheckin ? 'is-checked' : 'is-missing' }}">
                                        {{ $hasCheckin ? 'Checked in' : 'Not checked in' }}
                                    </span>
                                </td>
                                <td>{{ $attendance['last_check_in']?->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="table-actions">
                                    @if ($latestCheckin)
                                        <button type="button" class="btn-icon" data-checkin-view
                                            data-promoter-name="{{ $promoter->name }}"
                                            data-checkin-time="{{ $latestCheckin->check_in_at?->format('d M Y, H:i') ?? '-' }}"
                                            data-checkin-status="{{ $latestCheckin->status ?? '-' }}"
                                            data-checkin-lat="{{ $latestCheckin->latitude ?? '' }}"
                                            data-checkin-lng="{{ $latestCheckin->longitude ?? '' }}"
                                            data-checkin-image="{{ $checkinImageUrl ?? '' }}"
                                            title="View check-in" aria-label="View check-in">
                                            <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                                <path d="M10 4c4.4 0 7.4 3.2 8.4 5-.9 1.8-4 5-8.4 5S2.6 10.8 1.6 9c1-1.8 4-5 8.4-5zm0 2.2A2.8 2.8 0 1 0 10 13.8 2.8 2.8 0 0 0 10 6.2zm0 1.6A1.2 1.2 0 1 1 10 10.2 1.2 1.2 0 0 1 10 7.8z" fill="currentColor"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="muted text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted">No promoters assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-overlay" id="checkin-modal" aria-hidden="true">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="checkin-modal-title">
                <div class="modal-title" id="checkin-modal-title">Check-in details</div>
                <div class="modal-body">
                    <div class="text-sm" style="display: grid; gap: 6px;">
                        <div><strong>Promoter:</strong> <span id="checkin-modal-name">-</span></div>
                        <div><strong>Time:</strong> <span id="checkin-modal-time">-</span></div>
                        <div><strong>Status:</strong> <span id="checkin-modal-status">-</span></div>
                        <div><strong>Coordinates:</strong> <span id="checkin-modal-coords">-</span></div>
                    </div>
                    <div id="checkin-modal-image-wrap" style="margin-top: 12px;">
                        <img id="checkin-modal-image" class="checkin-image" src="" alt="Check-in image">
                        <div id="checkin-modal-image-empty" class="muted text-sm" style="display: none;">No image uploaded.</div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-modal-cancel>Close</button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('checkin-modal');
                const cancelBtn = modal?.querySelector('[data-modal-cancel]');
                const nameEl = document.getElementById('checkin-modal-name');
                const timeEl = document.getElementById('checkin-modal-time');
                const statusEl = document.getElementById('checkin-modal-status');
                const coordsEl = document.getElementById('checkin-modal-coords');
                const imageEl = document.getElementById('checkin-modal-image');
                const imageEmpty = document.getElementById('checkin-modal-image-empty');
                const imageWrap = document.getElementById('checkin-modal-image-wrap');

                const closeModal = () => {
                    modal?.classList.remove('is-open');
                    modal?.setAttribute('aria-hidden', 'true');
                };

                document.querySelectorAll('[data-checkin-view]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (nameEl) nameEl.textContent = button.getAttribute('data-promoter-name') || '-';
                        if (timeEl) timeEl.textContent = button.getAttribute('data-checkin-time') || '-';
                        if (statusEl) statusEl.textContent = button.getAttribute('data-checkin-status') || '-';
                        const lat = button.getAttribute('data-checkin-lat') || '';
                        const lng = button.getAttribute('data-checkin-lng') || '';
                        if (coordsEl) coordsEl.textContent = lat && lng ? `${lat}, ${lng}` : '-';

                        const imageUrl = button.getAttribute('data-checkin-image') || '';
                        if (imageEl && imageEmpty && imageWrap) {
                            if (imageUrl) {
                                imageEl.src = imageUrl;
                                imageEl.style.display = 'block';
                                imageEmpty.style.display = 'none';
                            } else {
                                imageEl.src = '';
                                imageEl.style.display = 'none';
                                imageEmpty.style.display = 'block';
                            }
                        }

                        modal?.classList.add('is-open');
                        modal?.setAttribute('aria-hidden', 'false');
                    });
                });

                cancelBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            });
        </script>

        <div class="tab-panel is-hidden" data-tab-panel="schedule" data-tab-group="promoter-views">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Time</th>
                            <th class="table-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($event->promoters as $promoter)
                            @php
                                $scheduleStartDate = $promoter->pivot?->start_date
                                    ? \Carbon\Carbon::parse($promoter->pivot->start_date)->format('Y-m-d')
                                    : '';
                                $scheduleEndDate = $promoter->pivot?->end_date
                                    ? \Carbon\Carbon::parse($promoter->pivot->end_date)->format('Y-m-d')
                                    : '';
                                $scheduleStartTime = $promoter->pivot?->start_time
                                    ? substr($promoter->pivot->start_time, 0, 5)
                                    : '';
                                $scheduleEndTime = $promoter->pivot?->end_time
                                    ? substr($promoter->pivot->end_time, 0, 5)
                                    : '';
                            @endphp
                            <tr>
                                <td>{{ $promoter->name }}</td>
                                <td>{{ $promoter->pivot?->start_date ? \Carbon\Carbon::parse($promoter->pivot->start_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $promoter->pivot?->end_date ? \Carbon\Carbon::parse($promoter->pivot->end_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $promoter->pivot?->start_time ?? '-' }} - {{ $promoter->pivot?->end_time ?? '-' }}</td>
                                <td class="table-actions">
                                    <button type="button" class="btn-icon" data-schedule-edit
                                        data-promoter-id="{{ $promoter->id }}"
                                        data-promoter-name="{{ $promoter->name }}"
                                        data-start-date="{{ $scheduleStartDate }}"
                                        data-end-date="{{ $scheduleEndDate }}"
                                        data-start-time="{{ $scheduleStartTime }}"
                                        data-end-time="{{ $scheduleEndTime }}"
                                        title="Edit schedule" aria-label="Edit schedule">
                                        <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                            <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                            <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">No promoters assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="schedule-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
        <div class="modal-title" id="schedule-modal-title">Edit schedule</div>
        <form method="POST" action="{{ route('customer.events.schedule.update', $event) }}" id="schedule-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="promoter_id" id="schedule-promoter-id">
            <div class="modal-body">
                <div class="text-sm" style="display: grid; gap: 10px;">
                    <div>
                        <strong>Promoter:</strong> <span id="schedule-promoter-name">-</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Start date</label>
                            <input type="date" name="start_date" id="schedule-start-date" class="input">
                        </div>
                        <div class="form-group">
                            <label>End date</label>
                            <input type="date" name="end_date" id="schedule-end-date" class="input">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Start time</label>
                            <input type="time" name="start_time" id="schedule-start-time" class="input">
                        </div>
                        <div class="form-group">
                            <label>End time</label>
                            <input type="time" name="end_time" id="schedule-end-time" class="input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions modal-actions-split">
                <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('schedule-modal');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');
        const promoterIdEl = document.getElementById('schedule-promoter-id');
        const promoterNameEl = document.getElementById('schedule-promoter-name');
        const startDateEl = document.getElementById('schedule-start-date');
        const endDateEl = document.getElementById('schedule-end-date');
        const startTimeEl = document.getElementById('schedule-start-time');
        const endTimeEl = document.getElementById('schedule-end-time');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-schedule-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                if (promoterIdEl) promoterIdEl.value = button.getAttribute('data-promoter-id') || '';
                if (promoterNameEl) promoterNameEl.textContent = button.getAttribute('data-promoter-name') || '-';
                if (startDateEl) startDateEl.value = button.getAttribute('data-start-date') || '';
                if (endDateEl) endDateEl.value = button.getAttribute('data-end-date') || '';
                if (startTimeEl) startTimeEl.value = button.getAttribute('data-start-time') || '';
                if (endTimeEl) endTimeEl.value = button.getAttribute('data-end-time') || '';

                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');
            });
        });

        cancelBtn?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });
</script>

<div class="tab-panel is-hidden" data-tab-panel="kpi" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Promoter KPI Targets</div>
            @if ($event->promoters->isEmpty())
                <div class="muted">No promoters assigned.</div>
            @else
                <form method="POST" action="{{ route('customer.events.kpis.update', $event) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Promoter</th>
                                    <th class="text-center">Sales Target</th>
                                    <th class="text-center">Engagements</th>
                                    <th class="text-center">Samplings</th>
                                    @foreach ($event->premiums as $premium)
                                        <th class="text-center">{{ $premium->gift_name }}</th>
                                    @endforeach
                                    <th class="text-center">Updated By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event->promoters as $promoter)
                                    @php
                                        $kpi = $promoterKpis->get($promoter->id);
                                        $promoterPremiumTargets = $premiumTargets->get($promoter->id, collect());
                                    @endphp
                                    <tr data-kpi-row>
                                        <td>{{ $promoter->name }}</td>
                                        <td class="text-right" data-kpi-cell>
                                            <span data-kpi-view>{{ old('kpis.' . $promoter->id . '.target_sales_amount', $kpi?->target_sales_amount) ?: '-' }}</span>
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_sales_amount]" value="{{ old('kpis.' . $promoter->id . '.target_sales_amount', $kpi?->target_sales_amount) }}" class="input text-right" step="0.01" min="0" data-kpi-input style="display: none;">
                                        </td>
                                        <td class="text-right" data-kpi-cell>
                                            <span data-kpi-view>{{ old('kpis.' . $promoter->id . '.target_engagements', $kpi?->target_engagements) ?: '-' }}</span>
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_engagements]" value="{{ old('kpis.' . $promoter->id . '.target_engagements', $kpi?->target_engagements) }}" class="input text-right" step="1" min="0" data-kpi-input style="display: none;">
                                        </td>
                                        <td class="text-right" data-kpi-cell>
                                            <span data-kpi-view>{{ old('kpis.' . $promoter->id . '.target_samplings', $kpi?->target_samplings) ?: '-' }}</span>
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_samplings]" value="{{ old('kpis.' . $promoter->id . '.target_samplings', $kpi?->target_samplings) }}" class="input text-right" step="1" min="0" data-kpi-input style="display: none;">
                                        </td>
                                        @foreach ($event->premiums as $premium)
                                            @php
                                                $premiumTarget = $promoterPremiumTargets->get($premium->id);
                                                $premiumValue = old('premium_targets.' . $promoter->id . '.' . $premium->id, $premiumTarget?->target_qty ?? 0);
                                            @endphp
                                            <td class="text-right" data-kpi-cell>
                                                <span data-kpi-view>{{ $premiumValue }}</span>
                                                <input type="number"
                                                    name="premium_targets[{{ $promoter->id }}][{{ $premium->id }}]"
                                                    value="{{ $premiumValue }}"
                                                    class="input text-right" step="1" min="0" data-kpi-input style="display: none;">
                                            </td>
                                        @endforeach
                                        <td>{{ $kpi?->updatedBy?->name ?? '-' }}</td>
                                        <td class="table-actions">
                                            <button type="button" class="btn-icon" data-kpi-edit title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn-icon" data-kpi-cancel title="Cancel" style="display: none;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="filters-actions" style="margin-top: 16px;">
                        <button class="btn btn-primary btn-block" type="submit">Save KPI Targets</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

<div class="tab-panel is-hidden" data-tab-panel="products" data-tab-group="event-details">
        <div class="card">
            <form method="GET" action="{{ route('customer.events.show', $event) }}" class="filters-bar" style="margin-bottom: 12px;" data-auto-filter data-filter-target="[data-tab-panel='products']">
                <input type="hidden" name="event_tab" value="products">
                <input type="hidden" name="product_tab" value="{{ request('product_tab', 'movements') }}" data-tab-store="product-views">
                <div class="filters-actions" style="width: 100%;">
                    <div class="filters-grid" style="flex: 1;">
                        <div class="form-group">
                            <input type="text" name="stock_search" value="{{ $stockFilters['search'] ?? '' }}" class="input" placeholder="Search product name">
                        </div>
                        <div class="form-group" data-filter-extra>
                            <select name="stock_type" class="select">
                                <option value="">All types</option>
                                <option value="in" @selected(($stockFilters['type'] ?? '') === 'in')>Stock In</option>
                                <option value="out" @selected(($stockFilters['type'] ?? '') === 'out')>Stock Out</option>
                            </select>
                        </div>
                        <div class="form-group" data-filter-extra>
                            <input type="date" name="stock_date" value="{{ $stockFilters['date'] ?? '' }}" class="input">
                        </div>
                    </div>
                    <div class="tabs" data-tabs data-tab-group="product-views" data-default-tab="{{ request('product_tab', 'movements') }}">
                        <div class="tabs-nav" role="tablist">
                            <button type="button" class="tab-button is-active" data-tab-target="movements" role="tab" aria-selected="true">Stock Movements</button>
                            <button type="button" class="tab-button" data-tab-target="manage" role="tab" aria-selected="false">Manage Products</button>
                        </div>
                    </div>
                </div>
            </form>
            @if ($event->products->isEmpty())
                <div class="muted">No products linked to this event.</div>
            @else
                <div class="tab-panel" data-tab-panel="movements" data-tab-group="product-views">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th class="text-right">Qty</th>
                                    <th>By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at?->format('d M Y, H:i') ?? '-' }}</td>
                                        <td>{{ $movement->product?->name ?? '-' }}</td>
                                        <td>{{ $movement->movement_type === 'in' ? 'Stock In' : 'Stock Out' }}</td>
                                        <td class="text-right">{{ $movement->quantity }}</td>
                                        <td>{{ $movement->createdBy?->name ?? '-' }}</td>
                                        <td>{{ $movement->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="muted">No stock movements yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-panel is-hidden" data-tab-panel="manage" data-tab-group="product-views">
                    <form method="POST" action="{{ route('customer.events.stock-balances.update', $event) }}">
                        @csrf
                        <div class="muted text-sm" style="margin-bottom: 12px;">
                            Add or reduce quantity to create stock movements. Price is used for KPI sales targets.
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-right">Current Balance</th>
                                        <th class="text-right">Adjust Qty</th>
                                        <th class="text-right">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($eventProducts as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td class="text-right">{{ $stockBalances[$product->id] ?? 0 }}</td>
                                            <td class="text-right">
                                                <input type="number" name="adjustments[{{ $product->id }}]"
                                                    value="{{ old('adjustments.' . $product->id) }}"
                                                    class="input text-right" step="1" placeholder="0">
                                            </td>
                                            <td class="text-right">
                                                <input type="number" name="prices[{{ $product->id }}]"
                                                    value="{{ old('prices.' . $product->id, $product->pivot?->unit_price) }}"
                                                    class="input text-right" min="0" step="0.01" placeholder="0.00">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="filters-actions" style="margin-top: 16px;">
                            <button class="btn btn-primary btn-block" type="submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

<div class="tab-panel is-hidden" data-tab-panel="premiums" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Event Premiums</div>
            @if ($event->premiums->isEmpty())
                <div class="muted">No premiums linked to this event.</div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gift</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->premiums as $premium)
                                <tr>
                                    <td>{{ $premium->gift_name }}</td>
                                    <td>{{ $premium->mechanic_description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
</div>

<div class="tab-panel is-hidden" data-tab-panel="submissions" data-tab-group="event-details">
    <div class="card">
        <form method="GET" action="{{ route('customer.events.show', $event) }}" class="filters-bar" style="margin-bottom: 20px;" data-auto-filter data-filter-target="[data-tab-panel='submissions']">
            <input type="hidden" name="event_tab" value="submissions">
            <div class="filters-grid">
                <div class="form-group">
                    <label for="submission_promoter" class="label">Promoter</label>
                    <select name="submission_promoter" id="submission_promoter" class="select">
                        <option value="">All Promoters</option>
                        @foreach($event->promoters as $promoter)
                            <option value="{{ $promoter->id }}" @selected(($submissionFilters['promoter'] ?? '') == $promoter->id)>
                                {{ $promoter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="submission_date" class="label">Date</label>
                    <input type="date" name="submission_date" id="submission_date" value="{{ $submissionFilters['date'] ?? '' }}" class="input">
                </div>
            </div>
        </form>

        <div class="stat-label" style="margin-bottom: 16px;">
            Hourly Report Submissions
            <span style="color: var(--muted); font-weight: 400; font-size: 14px;">({{ $filteredSubmissions->count() }} reports)</span>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Hour</th>
                        <th>Promoter</th>
                        <th class="text-right">Sales Amount</th>
                        <th class="text-right">Engagements</th>
                        <th class="text-right">Samplings</th>
                        <th>Products Sold</th>
                        <th>Premiums</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filteredSubmissions as $report)
                        <tr>
                            <td>{{ $report->report_date?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <span style="font-weight: 600; color: var(--brand);">
                                    {{ $report->report_hour }}:00 - {{ $report->report_hour + 1 }}:00
                                </span>
                            </td>
                            <td>{{ $report->promoter?->name ?? '-' }}</td>
                            <td class="text-right">
                                <span style="font-weight: 600; color: #0f6d57;">
                                    RM {{ number_format($report->total_sales_amount ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="text-right">{{ number_format($report->engagements_count ?? 0) }}</td>
                            <td class="text-right">{{ number_format($report->samplings_count ?? 0) }}</td>
                            <td>
                                @if($report->items && $report->items->count() > 0)
                                    <div style="font-size: 13px;">
                                        @foreach($report->items->take(3) as $item)
                                            <div style="margin-bottom: 2px;">
                                                {{ $item->product?->name ?? 'Unknown' }}: 
                                                <span style="font-weight: 600;">{{ $item->quantity_sold }}</span>
                                            </div>
                                        @endforeach
                                        @if($report->items->count() > 3)
                                            <div style="color: var(--muted); font-size: 12px;">
                                                +{{ $report->items->count() - 3 }} more
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($report->premiums && $report->premiums->count() > 0)
                                    <div style="font-size: 13px;">
                                        @foreach($report->premiums->groupBy('premium_id') as $premiumId => $items)
                                            @php
                                                $premiumName = $items->first()?->premium?->gift_name ?? 'Premium';
                                                $premiumQty = $items->sum('quantity');
                                            @endphp
                                            <div style="margin-bottom: 2px;">
                                                {{ $premiumName }}:
                                                <span style="font-weight: 600;">{{ $premiumQty }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--muted);">
                                    {{ $report->created_at?->format('d M Y') }}<br>
                                    {{ $report->created_at?->format('H:i') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="muted" style="text-align: center; padding: 40px;">
                                @if($submissionFilters['promoter'] || $submissionFilters['date'])
                                    No submissions found for the selected filters.
                                @else
                                    No hourly reports submitted yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($filteredSubmissions->count() > 0)
        <div style="margin-top: 20px; padding: 16px; background: var(--surface-2); border-radius: 10px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Total Sales</div>
                    <div style="font-size: 24px; font-weight: 700; color: #1e4f8f;">
                        RM {{ number_format($filteredSubmissions->sum('total_sales_amount'), 2) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Total Engagements</div>
                    <div style="font-size: 24px; font-weight: 700; color: #7c3aed;">
                        {{ number_format($filteredSubmissions->sum('engagements_count')) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Total Samplings</div>
                    <div style="font-size: 24px; font-weight: 700; color: #ea580c;">
                        {{ number_format($filteredSubmissions->sum('samplings_count')) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Average per Hour</div>
                    <div style="font-size: 24px; font-weight: 700; color: #0f6d57;">
                        RM {{ number_format($filteredSubmissions->avg('total_sales_amount'), 2) }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="tab-panel is-hidden" data-tab-panel="activity" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Activity Log</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>By</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activityItems as $item)
                            <tr>
                                <td>{{ $item['timestamp']?->format('d M Y, H:i') ?? '-' }}</td>
                                <td>{{ $item['title'] }}</td>
                                <td>{{ $item['by'] ?? '-' }}</td>
                                <td>{{ $item['details'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted">No activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
@endsection
