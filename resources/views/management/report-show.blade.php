@extends('layouts.app')

@section('page_title', 'Report Details')
@section('page_desc', 'Audit-ready view with authoritative context and itemized breakdown.')
@section('page_actions')
    <a href="{{ route('management.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
@endsection

@section('content')
<div class="split-view">
    <div class="card">
        <div class="stat-label">Authority Context</div>
        <div class="form-grid text-sm">
            <div><strong>Promoter:</strong> {{ $report->promoter?->name }}</div>
            <div><strong>Location:</strong> {{ $report->location?->name }}</div>
            <div><strong>Date:</strong> {{ $report->report_date->toDateString() }}</div>
            <div><strong>Hour:</strong> {{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</div>
            <div><strong>Sales:</strong> RM {{ number_format($report->total_sales_amount, 2) }}</div>
            <div><strong>Engagements:</strong> {{ $report->engagements_count }}</div>
            <div><strong>Samplings:</strong> {{ $report->samplings_count }}</div>
        </div>
    </div>

    <div class="card">
        <div class="form-grid">
            <div class="card">
                <div class="stat-label">Product Sales</div>
                <ul class="text-sm">
                    @forelse ($report->items as $item)
                        <li class="form-grid" style="grid-template-columns: 1fr auto;">
                            <span>{{ $item->product?->name }}</span>
                            <span>{{ $item->quantity_sold }}</span>
                        </li>
                    @empty
                        <li class="muted">No items recorded.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card">
                <div class="stat-label">Premium Redemptions</div>
                <ul class="text-sm">
                    @php
                        $tier1 = $report->premiums->where('tier', 1)->sum('quantity');
                        $tier2 = $report->premiums->where('tier', 2)->sum('quantity');
                    @endphp
                    <li class="form-grid" style="grid-template-columns: 1fr auto;">
                        <span>Tier 1</span>
                        <span>{{ $tier1 }}</span>
                    </li>
                    <li class="form-grid" style="grid-template-columns: 1fr auto;">
                        <span>Tier 2</span>
                        <span>{{ $tier2 }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
